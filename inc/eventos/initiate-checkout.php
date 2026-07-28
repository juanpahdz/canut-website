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
 * Sends InitiateCheckout for the current cart. Short dedup window (same
 * reasoning as lead.php's own dedup_key - an accidental double submit, not a
 * genuinely repeated action) rather than none: woocommerce_before_checkout_form
 * was assumed to fire exactly once per real checkout page load, but in
 * practice a single visit can trigger it twice in quick succession (a
 * double-click on the "go to checkout" link/button, or the browser
 * prefetching it before the real navigation) - two distinct event_ids for the
 * same cart landing in Events Manager a second apart. 15s is long enough to
 * absorb that, short enough that a visitor who actually leaves and comes back
 * to retry checkout still gets counted as a new InitiateCheckout almost
 * immediately, matching how a client-side Facebook pixel would fire this
 * event on every genuine checkout page load.
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
    'dedup_key'   => WC()->session ? WC()->session->get_customer_id() : '',
    'dedup_ttl'   => 15,
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
