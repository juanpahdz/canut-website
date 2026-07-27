<?php
/**
 * Order summary sidebar (CANUT redesign).
 *
 * Overrides WooCommerce's default checkout/review-order.php. Restructured
 * into a single card (product rows, totals, place-order button, trust
 * badges) instead of core's bare <table> - matches the block checkout's
 * sidebar this replaces, just with real classic-checkout markup instead of
 * wc-block-* elements.
 *
 * No coupon form here - core's own is itself a <form>, and this whole card
 * already sits inside <form class="checkout"> (woocommerce/checkout/form-checkout.php's
 * .checkout-form-canut-sidebar) - a nested <form> is invalid HTML, silently
 * dropped by the browser (keeps its <p> children but not the <form> tag
 * itself, along with the id/style="display:none" WooCommerce's own
 * checkout.js coupon toggle (wc_checkout_coupons in assets/js/frontend/checkout.js)
 * depends on), breaking both the show/hide toggle and the apply-coupon AJAX
 * submit. Rendered after this <form> closes instead (see form-checkout.php,
 * under the "Método de pago" step) - its default hook
 * (woocommerce_before_checkout_form) is removed in inc/hooks/checkout.php so
 * it only ever renders there.
 *
 * The terms checkbox + real #place_order button + checkout nonce normally
 * live in checkout/payment.php - moved here instead (see that override's
 * own docblock) so the CTA sits in the summary card per the design, without
 * changing anything about how the order actually submits.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;
?>

<?php
/**
 * .woocommerce-checkout-review-order-table is what WooCommerce's own
 * checkout.js (assets/js/frontend/checkout.js, update_order_review success
 * handler) actually looks for to swap in fresh fragments after any AJAX
 * recalculation (address change, shipping/payment method change, and now
 * the quantity stepper below, modules/checkout-canut.js) - core's default
 * template has that class on its <table>; without it here, checkout.js's
 * $(key).replaceWith(value) silently matches nothing and #order_review's
 * content never actually refreshes. data-checkout-summary-canut-count feeds
 * the mobile fixed bar's item-count text the same way .order-total .amount
 * already feeds its total (syncMobileSummaryTotal(), modules/checkout-canut.js).
 */
?>
<div
  class="checkout-summary-canut-card woocommerce-checkout-review-order-table"
  data-checkout-summary-canut-count="<?php echo esc_attr( sprintf(
    /* translators: %d: number of items in the cart. */
    _n( '%d producto', '%d productos', WC()->cart->get_cart_contents_count(), 'air-light' ),
    WC()->cart->get_cart_contents_count()
  ) ); ?>"
>
  <h2 class="checkout-summary-canut-title"><?php esc_html_e( 'Resumen del pedido', 'air-light' ); ?></h2>

  <div class="checkout-summary-canut-items">
    <?php
    do_action( 'woocommerce_review_order_before_cart_contents' );

    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
      $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

      if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
        continue;
      }

      $max_qty            = $_product->get_max_purchase_quantity();
      $short_description  = $_product->get_short_description();
      ?>
      <div class="checkout-summary-canut-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
        <div class="checkout-summary-canut-item-thumb">
          <?php echo wp_kses_post( $_product->get_image( 'thumbnail' ) ); ?>
        </div>
        <div class="checkout-summary-canut-item-body">
          <p class="checkout-summary-canut-item-name">
            <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
          </p>
          <?php if ( $short_description ) : ?>
            <p class="checkout-summary-canut-item-description"><?php echo esc_html( checkout_truncate_description( $short_description ) ); ?></p>
          <?php endif; ?>
          <?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
          <div class="cart-drawer-canut-item-row">
            <div class="cart-drawer-canut-stepper" role="group" aria-label="<?php esc_attr_e( 'Cantidad', 'air-light' ); ?>">
              <button type="button" data-checkout-summary-canut-qty="decrease" <?php disabled( $cart_item['quantity'] <= 1 ); ?> aria-label="<?php esc_attr_e( 'Disminuir cantidad', 'air-light' ); ?>">
                <?php require get_theme_file_path( 'assets/svg/icon-minus-circle.svg' ); ?>
              </button>
              <output aria-live="polite"><?php echo esc_html( $cart_item['quantity'] ); ?></output>
              <button type="button" data-checkout-summary-canut-qty="increase" <?php disabled( $max_qty > 0 && $cart_item['quantity'] >= $max_qty ); ?> aria-label="<?php esc_attr_e( 'Aumentar cantidad', 'air-light' ); ?>">
                <?php require get_theme_file_path( 'assets/svg/icon-plus-circle.svg' ); ?>
              </button>
            </div>
            <span class="checkout-summary-canut-item-total">
              <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </span>
          </div>
        </div>
      </div>

      <?php render_qty_discount_upsell( $cart_item_key, $_product, $cart_item['quantity'] ); ?>
      <?php
    }

    do_action( 'woocommerce_review_order_after_cart_contents' );
    ?>
  </div>

  <table class="checkout-summary-canut-totals">
    <tbody>
      <tr class="cart-subtotal">
        <th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
        <td><?php wc_cart_totals_subtotal_html(); ?></td>
      </tr>

      <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
        <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
          <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
          <td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
        </tr>
      <?php endforeach; ?>

      <?php
      /**
       * Shipping method selection lives in its own numbered step now
       * (checkout_render_shipping_methods() in inc/hooks/checkout.php,
       * rendered by woocommerce/checkout/form-checkout.php) instead of here -
       * wc_cart_totals_shipping_html() renders the exact same radio inputs
       * (same name/value per rate), so keeping both would just be the same
       * picker duplicated in two places. Same un-pairing woocommerce_checkout_payment()
       * already gets (see checkout_unhook_payment_from_order_review() in
       * inc/hooks/checkout.php) - core's own totals table normally pairs
       * shipping selection with the cost breakdown, this only keeps the cost
       * side.
       */
      ?>
      <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
        <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
        <tr class="woocommerce-shipping-totals shipping">
          <th><?php esc_html_e( 'Envío', 'woocommerce' ); ?></th>
          <td><?php checkout_render_chosen_shipping_method_label(); ?></td>
        </tr>
        <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
      <?php endif; ?>

      <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
        <tr class="fee">
          <th><?php echo esc_html( $fee->name ); ?></th>
          <td><?php wc_cart_totals_fee_html( $fee ); ?></td>
        </tr>
      <?php endforeach; ?>

      <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
        <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
          <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
            <tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
              <th><?php echo esc_html( $tax->label ); ?></th>
              <td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else : ?>
          <tr class="tax-total">
            <th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
            <td><?php wc_cart_totals_taxes_total_html(); ?></td>
          </tr>
        <?php endif; ?>
      <?php endif; ?>

      <?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

      <tr class="order-total">
        <th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
        <td><?php wc_cart_totals_order_total_html(); ?></td>
      </tr>

      <?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
    </tbody>
  </table>

  <div class="checkout-summary-canut-actions">
    <noscript>
      <?php
      /* translators: $1 and $2 opening and closing emphasis tags respectively */
      printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ), '<em>', '</em>' );
      ?>
      <br/><button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
    </noscript>

    <?php wc_get_template( 'checkout/terms.php' ); ?>

    <?php do_action( 'woocommerce_review_order_before_submit' ); ?>

    <button type="submit" class="button-canut-base button-canut-checkout checkout-summary-canut-submit" name="woocommerce_checkout_place_order" id="place_order" value="<?php echo esc_attr( $order_button_text ?? __( 'Finalizar compra', 'air-light' ) ); ?>" data-value="<?php echo esc_attr( $order_button_text ?? __( 'Finalizar compra', 'air-light' ) ); ?>">
      <?php require get_theme_file_path( 'assets/svg/icon-lock.svg' ); ?>
      <?php echo esc_html( $order_button_text ?? __( 'Finalizar compra', 'air-light' ) ); ?>
    </button>

    <?php do_action( 'woocommerce_review_order_after_submit' ); ?>

    <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
  </div>

  <?php checkout_render_trust_badges(); ?>
</div>
