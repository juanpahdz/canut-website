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
 * Sends InitiateCheckout for the current cart. No dedup guard here on
 * purpose, same reasoning as ViewContent: woocommerce_before_checkout_form
 * fires once per real checkout page load, and a visitor leaving checkout and
 * coming back later to try again is a genuine new InitiateCheckout, not a
 * repeat to suppress - matching how a client-side Facebook pixel would fire
 * this event on every checkout page load too.
 */
function send_initiate_checkout_event() {
  if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
    return;
  }

  $cart_contents = facebook_capi_cart_contents( WC()->cart );

  if ( ! $cart_contents['contents'] ) {
    return;
  }

  send_facebook_event( 'InitiateCheckout', [
    'custom_data' => array_merge( [ 'content_type' => 'product' ], $cart_contents ),
  ] );

  // TEMPORARY debug aid, WP_DEBUG only - see facebook_capi_debug_log()'s own
  // docblock (inc/eventos/facebook-conversions-api.php). Safe to remove once
  // InitiateCheckout is confirmed firing on checkout page load.
  if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    printf(
      '<script>console.log("[FB CAPI] InitiateCheckout:", %s);</script>',
      wp_json_encode( facebook_capi_debug_log() )
    );
  }
} // end send_initiate_checkout_event
