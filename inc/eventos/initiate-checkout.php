<?php
/**
 * InitiateCheckout: fired when a visitor reaches the checkout page with a
 * non-empty cart - marks where the funnel goes from "has items in cart" to
 * "started paying", so drop-off before vs. after this point can be measured.
 *
 * woocommerce_before_checkout_form fires from inside the classic
 * [woocommerce_checkout] shortcode's own template (see
 * woocommerce/checkout/form-checkout.php) - only the real checkout page, not
 * order-pay or the thank-you page, each of which render through different
 * templates entirely.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_before_checkout_form', __NAMESPACE__ . '\send_initiate_checkout_event' );

/**
 * Sends InitiateCheckout for the current cart. Deduped per (session, cart
 * contents) for 30 minutes, so reloading the checkout page repeatedly with
 * the same cart doesn't refire the event - it fires again if the cart
 * actually changes (item added/removed/qty changed) or after the window
 * lapses.
 */
function send_initiate_checkout_event() {
  if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
    return;
  }

  $cart          = WC()->cart;
  $cart_contents = facebook_capi_cart_contents( $cart );

  if ( ! $cart_contents['contents'] ) {
    return;
  }

  send_facebook_event( 'InitiateCheckout', [
    'dedup_key'   => facebook_capi_session_id() . '|' . $cart->get_cart_hash(),
    'dedup_ttl'   => 30 * MINUTE_IN_SECONDS,
    'custom_data' => array_merge( [ 'content_type' => 'product' ], $cart_contents ),
  ] );
} // end send_initiate_checkout_event
