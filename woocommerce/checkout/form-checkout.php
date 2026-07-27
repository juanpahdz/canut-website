<?php
/**
 * Checkout form (CANUT redesign).
 *
 * Overrides WooCommerce's default checkout/form-checkout.php. Restructures
 * the single-column, hook-driven default into CANUT's numbered 4-step left
 * column ("Información de contacto" / "Envío" / "Método de pago" / "Método
 * de envío") plus a sticky order-summary card on the right - see
 * inc/hooks/checkout.php for checkout_step_header() and the render helpers
 * used below, and views/_checkout-canut.scss for the styling.
 *
 * All 4 steps form a sequential wizard (modules/checkout-steps-canut.js):
 * "Información de contacto" starts open, the other three start locked
 * (`is-locked`, .checkout-step-canut-body hidden, a placeholder message shown
 * instead) since each depends on the one before it - a shipping method in
 * particular can't be picked/quoted correctly until an address AND a payment
 * method (COD vs online - see JPIODFW_Shipping::order_is_cod()) are both
 * known. Every step but the last has a "Continuar" button that validates its
 * own required fields, collapses it into a read-only summary (`is-confirmed`,
 * a "Modificar" button to reopen it) and unlocks the next step; confirming
 * "Método de pago" also triggers a real `update_checkout` (core's own AJAX
 * cycle) so the shipping cards that unlock right after already reflect the
 * chosen payment method. "Método de envío" itself has no Continuar button -
 * it's the last step before the sidebar's real "Finalizar compra" button.
 *
 * Field composition is deliberately split by hand (not the stock
 * form-billing.php/form-shipping.php, which each render their whole
 * fieldset as one uninterrupted block) so billing_email/billing_phone can
 * sit in step 1 while the rest of the billing address sits in step 2,
 * matching the Figma layout. Billing is treated as the one and only
 * address (no "ship to a different address" section rendered) - WooCommerce
 * itself already falls back to using billing as the shipping address
 * whenever that section is absent/unchecked, which is exactly the single-
 * address flow this design calls for.
 *
 * Hook names match core's own template where they still apply, so any
 * plugin already hooking into the classic checkout (e.g. a payment gateway
 * adding a notice) still lands in a sensible spot.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 *
 * @var WC_Checkout $checkout
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

/**
 * Restored to match core's own form-checkout.php position (before the
 * registration check, before <form> even opens) - this rewrite had dropped
 * it entirely, which silently broke inc/eventos/initiate-checkout.php (hooked
 * here) and made the InitiateCheckout Conversions API event never fire.
 */
do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
  echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
  return;
}

$billing_fields = $checkout->get_checkout_fields( 'billing' );
$order_fields = $checkout->get_checkout_fields( 'order' );

$contact_keys = [ 'billing_email', 'billing_phone' ];
$address_keys = array_diff( array_keys( $billing_fields ), $contact_keys );
?>

<?php
/**
 * woocommerce/global/wrapper-start.php opens a bare <main class="site-main
 * woocommerce-main"> with none of the width classes page.php's own <main>
 * has - every other WooCommerce template (archive-product.php,
 * single-product.php) supplies its own width via .wrap-canut, the same
 * sitewide wide-content-container utility, rather than relying on the
 * wrapper for it. Without it here, this form was inheriting whatever
 * (much narrower) default content width applies instead, squeezing the
 * two-column layout enough to wrap text that fits fine in the design.
 */
?>
<div class="wrap-canut">

<form name="checkout" method="post" class="checkout woocommerce-checkout checkout-form-canut" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

  <div class="checkout-form-canut-columns">
    <div class="checkout-form-canut-main">

      <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

      <div id="customer_details">
        <?php
        /**
         * Contact step - first link in the sequential wizard (see this
         * file's own docblock). .checkout-step-canut-body wraps the fields
         * grid AND the additional-phone repeater AND the Continue button (not
         * just the field grid) so all three hide together once this step
         * collapses to its summary.
         */
        ?>
        <section class="checkout-step-canut" data-step="1">
          <?php checkout_step_header( 1, __( 'Información de contacto', 'air-light' ) ); ?>
          <div class="checkout-step-canut-body">
            <div class="checkout-step-canut-body-grid">
              <?php foreach ( $contact_keys as $key ) : ?>
                <?php if ( ! isset( $billing_fields[ $key ] ) ) : ?>
                  <?php continue; ?>
                <?php endif; ?>

                <?php if ( 'billing_phone' === $key ) : ?>
                  <?php checkout_render_phone_field( $billing_fields[ $key ], $checkout->get_value( $key ), $checkout->get_value( 'billing_phone_country_code' ) ); ?>
                <?php else : ?>
                  <?php woocommerce_form_field( $key, $billing_fields[ $key ], $checkout->get_value( $key ) ); ?>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>

            <?php checkout_render_additional_phones(); ?>

            <?php checkout_render_data_consent_field(); ?>

            <button type="button" class="button-canut-base button-canut-primary is-full-width checkout-step-canut-continue" data-step-continue="1">
              <?php esc_html_e( 'Continuar a envío', 'air-light' ); ?>
            </button>
          </div>

          <div class="checkout-step-canut-summary" data-step-summary="1">
            <div class="checkout-step-canut-summary-row">
              <div class="checkout-step-canut-summary-text">
                <p class="checkout-step-canut-summary-title"><?php esc_html_e( 'Información de contacto', 'air-light' ); ?></p>
                <p class="checkout-step-canut-summary-detail" data-summary-field="contact"></p>
              </div>
              <button type="button" class="checkout-step-canut-summary-edit" data-step-edit="1">
                <?php esc_html_e( 'Modificar', 'air-light' ); ?>
              </button>
            </div>
          </div>
        </section>

        <?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

        <?php
        /**
         * Address step - starts locked until "Información de contacto" is
         * confirmed. .checkout-step-canut-body wraps the fields grid AND the
         * shipping-info banner AND the Continue button (not just the field
         * grid, unlike step 1's own grid) so all three hide together once
         * this step collapses to its summary - a sibling
         * .checkout-step-canut-summary (built/populated client-side by
         * modules/checkout-steps-canut.js from the actual field values on
         * "Continuar") takes its place, matching the "Delivering to X /
         * Change" pattern.
         */
        ?>
        <section class="checkout-step-canut is-locked" data-step="2" data-lockable="1">
          <?php checkout_step_header( 2, __( 'Envío', 'air-light' ) ); ?>
          <div class="checkout-step-canut-body">
            <div class="checkout-step-canut-body-grid">
              <?php foreach ( $address_keys as $key ) : ?>
                <?php woocommerce_form_field( $key, $billing_fields[ $key ], $checkout->get_value( $key ) ); ?>
              <?php endforeach; ?>

              <?php foreach ( $order_fields as $key => $field ) : ?>
                <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
              <?php endforeach; ?>
            </div>

            <?php checkout_render_shipping_info_box(); ?>

            <button type="button" class="button-canut-base button-canut-primary is-full-width checkout-step-canut-continue" data-step-continue="2">
              <?php esc_html_e( 'Continuar a método de pago', 'air-light' ); ?>
            </button>
          </div>

          <div class="checkout-step-canut-summary" data-step-summary="2">
            <div class="checkout-step-canut-summary-row">
              <div class="checkout-step-canut-summary-text">
                <p class="checkout-step-canut-summary-title">
                  <?php esc_html_e( 'Enviando a', 'air-light' ); ?> <span data-summary-field="name"></span>
                </p>
                <p class="checkout-step-canut-summary-detail" data-summary-field="address"></p>
              </div>
              <button type="button" class="checkout-step-canut-summary-edit" data-step-edit="2">
                <?php esc_html_e( 'Modificar', 'air-light' ); ?>
              </button>
            </div>
          </div>

          <div class="checkout-step-canut-locked-message">
            <p class="checkout-step-canut-locked-message-default"><?php esc_html_e( 'Completa la información de contacto para continuar.', 'air-light' ); ?></p>
          </div>
        </section>

        <?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
      </div>

      <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

      <?php
      /**
       * Payment step - starts locked (`is-locked`, see this file's own
       * docblock) until "Envío" is confirmed. Coupon field
       * (checkout_render_coupon_field(), inc/hooks/checkout.php) lives
       * inside this same step's body, right under the payment cards -
       * unlike core's own coupon widget (a real <form>, still rendered
       * separately - see woocommerce/checkout/review-order.php's docblock
       * for why it can't nest inside <form class="checkout">),
       * checkout_render_coupon_field() renders plain (non-<form>) markup, so
       * it can sit inside this <section> - itself inside the main checkout
       * <form> - without any nested-form problem. Its "Aplicar"/toggle
       * behaviour is wired in modules/checkout-steps-canut.js via a direct
       * call to WooCommerce's own apply_coupon AJAX endpoint instead of
       * relying on core's form-submit-bound checkout.js handler (which
       * needs the real <form class="checkout_coupon"> this isn't).
       */
      ?>
      <section class="checkout-step-canut is-locked" data-step="3" data-lockable="1">
        <?php checkout_step_header( 3, __( 'Método de pago', 'air-light' ) ); ?>
        <div class="checkout-step-canut-body">
          <?php
          // Core pairs woocommerce_checkout_payment() (renders payment.php)
          // with woocommerce_order_review() on the SAME 'woocommerce_checkout_order_review'
          // action (priorities 10/20) - both would otherwise land in the
          // sidebar below. inc/hooks/checkout.php un-pairs them so this is
          // the only place it renders, matching the Figma layout (payment
          // step in the left column, not inside the order-summary card).
          \woocommerce_checkout_payment();
          ?>

          <?php checkout_render_coupon_field(); ?>

          <button type="button" class="button-canut-base button-canut-primary is-full-width checkout-step-canut-continue" data-step-continue="3">
            <?php esc_html_e( 'Continuar a método de envío', 'air-light' ); ?>
          </button>
        </div>

        <div class="checkout-step-canut-summary" data-step-summary="3">
          <div class="checkout-step-canut-summary-row">
            <div class="checkout-step-canut-summary-text">
              <p class="checkout-step-canut-summary-title"><?php esc_html_e( 'Método de pago', 'air-light' ); ?></p>
              <p class="checkout-step-canut-summary-detail" data-summary-field="payment"></p>
            </div>
            <button type="button" class="checkout-step-canut-summary-edit" data-step-edit="3">
              <?php esc_html_e( 'Modificar', 'air-light' ); ?>
            </button>
          </div>
        </div>

        <div class="checkout-step-canut-locked-message">
          <p class="checkout-step-canut-locked-message-default"><?php esc_html_e( 'Completa el paso de envío para continuar.', 'air-light' ); ?></p>
        </div>
      </section>

      <?php
      /**
       * Shipping method step - final step, full-width cards
       * (checkout_render_shipping_methods() in inc/hooks/checkout.php), one
       * per available rate for the current address (whatever
       * wc-dropi-integration's "Dropi - Cotizador de flete" shipping method
       * and/or "Envío gratis" resolve for the matching shipping zone - see
       * that function's own docblock). Wrapped in
       * .checkout-shipping-methods-canut so classic checkout's own AJAX
       * cycle (address/city/payment-method change) can refresh just these
       * cards in place - see checkout_shipping_methods_fragment(), hooked to
       * woocommerce_update_order_review_fragments. Starts locked until
       * "Método de pago" is confirmed - Dropi's freight quote depends on
       * whether the order is COD (JPIODFW_Shipping::order_is_cod(), reads
       * the chosen payment method from session), so showing these before a
       * payment method is picked could show since-corrected pricing. Whole
       * step skipped when the cart doesn't need shipping (e.g.
       * downloadable-only), same guard woocommerce/checkout/review-order.php
       * already uses around wc_cart_totals_shipping_html().
       */
      ?>
      <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
        <section class="checkout-step-canut is-locked" data-step="4" data-lockable="1">
          <?php checkout_step_header( 4, __( 'Método de envío', 'air-light' ) ); ?>
          <div class="checkout-step-canut-body">
            <div class="checkout-shipping-methods-canut">
              <?php checkout_render_shipping_methods(); ?>
            </div>
          </div>

          <div class="checkout-step-canut-locked-message">
            <p class="checkout-step-canut-locked-message-default"><?php esc_html_e( 'Completa el método de pago para continuar.', 'air-light' ); ?></p>
            <p class="checkout-step-canut-locked-message-loading"><?php esc_html_e( 'Calculando opciones de envío según tu método de pago…', 'air-light' ); ?></p>
          </div>
        </section>
      <?php endif; ?>

      <?php checkout_render_help_banner(); ?>

    </div>

    <div class="checkout-form-canut-sidebar">
      <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
      <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

      <div id="order_review" class="woocommerce-checkout-review-order">
        <?php do_action( 'woocommerce_checkout_order_review' ); ?>
      </div>

      <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
    </div>
  </div>

</form>

</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
