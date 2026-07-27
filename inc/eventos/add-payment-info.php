<?php
/**
 * AddPaymentInfo: fired when a customer has a payment method selected at
 * checkout, right before they place the order - narrows the funnel further
 * between "reached checkout" (InitiateCheckout) and "order placed".
 *
 * woocommerce_checkout_update_order_review is core's own AJAX hook, fired
 * every time classic checkout's checkout.js posts an updated order review
 * (address change, payment method change, shipping method change, coupon
 * apply - see inc/hooks/checkout.php for other consumers of this same AJAX
 * cycle). $post_data is the serialized checkout form, so it's only actually
 * an AddPaymentInfo-worthy update when it carries a payment_method.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_checkout_update_order_review', __NAMESPACE__ . '\send_add_payment_info_event' );

/**
 * @param string $post_data Serialized checkout form fields (query-string encoded).
 */
function send_add_payment_info_event( $post_data ) {
  if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
    return;
  }

  parse_str( (string) $post_data, $data );

  $payment_method = isset( $data['payment_method'] ) ? sanitize_text_field( $data['payment_method'] ) : '';

  if ( ! $payment_method ) {
    return;
  }

  $cart          = WC()->cart;
  $cart_contents = facebook_capi_cart_contents( $cart );

  if ( ! $cart_contents['contents'] ) {
    return;
  }

  send_facebook_event( 'AddPaymentInfo', [
    // Refires if the customer switches payment method or the cart changes,
    // not on every unrelated field edit the same AJAX cycle also runs for.
    'dedup_key'   => facebook_capi_session_id() . '|' . $payment_method . '|' . $cart->get_cart_hash(),
    'dedup_ttl'   => 30 * MINUTE_IN_SECONDS,
    'custom_data' => array_merge( [ 'content_type' => 'product' ], $cart_contents ),
  ] );
} // end send_add_payment_info_event
