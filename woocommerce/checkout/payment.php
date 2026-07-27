<?php
/**
 * Checkout payment section (CANUT redesign).
 *
 * Overrides WooCommerce's default checkout/payment.php. Core's own version
 * bundles the gateway list AND the terms checkbox + real place-order button
 * + nonce together in one block - this only renders the gateway list (each
 * card via woocommerce/checkout/payment-method.php), since the CANUT design
 * puts the terms/button in the order-summary sidebar instead of after the
 * payment step. See woocommerce/checkout/review-order.php for where that
 * moved to (same real button/nonce markup, just relocated - nothing about
 * how the order actually submits changes).
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
  do_action( 'woocommerce_review_order_before_payment' );
}
?>

<div id="payment" class="woocommerce-checkout-payment">
  <?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
    <ul class="wc_payment_methods payment_methods methods payment-option-canut-list">
      <?php
      if ( ! empty( $available_gateways ) ) {
        foreach ( $available_gateways as $gateway ) {
          wc_get_template( 'checkout/payment-method.php', [ 'gateway' => $gateway ] );
        }
      } else {
        echo '<li>';
        wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ), 'notice' );
        echo '</li>';
      }
      ?>
    </ul>
  <?php endif; ?>
</div>

<?php
if ( ! wp_doing_ajax() ) {
  do_action( 'woocommerce_review_order_after_payment' );
}
