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
 * an AddPaymentInfo-worthy update when it carries a payment_method - and
 * deduped against the WooCommerce session (see add-contact-info.php for the
 * same mechanism) so it only sends once per distinct payment method/cart
 * combo for the whole checkout session, not on every unrelated field edit
 * the same AJAX cycle also runs for, and not a spurious resend just because
 * time passed with the same method still selected.
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

  $value_hash = md5( $payment_method . '|' . $cart->get_cart_hash() );

  if ( facebook_capi_session_value_unchanged( 'payment_info', $value_hash ) ) {
    return;
  }

  $sent = send_facebook_event( 'AddPaymentInfo', [
    'custom_data' => array_merge( [ 'content_type' => 'product' ], $cart_contents ),
  ] );

  if ( $sent ) {
    facebook_capi_mark_session_value_sent( 'payment_info', $value_hash );
  }
} // end send_add_payment_info_event
