<?php
/**
 * ViewContent: fired when a visitor opens a single product page - the base
 * event Facebook's dynamic ads (showing someone, later, the exact product
 * they looked at) are built on. Sent from the backend on the request that
 * renders the page (see facebook-conversions-api.php for why: no client-side
 * fbq() calls anywhere in this theme, so there is only one place this event
 * could ever fire from).
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'template_redirect', __NAMESPACE__ . '\send_view_content_event' );

/**
 * Sends ViewContent for the product being viewed. No dedup guard here on
 * purpose: template_redirect fires exactly once per request already, and
 * every real page load - including a visitor leaving and coming back to the
 * same product later - is its own genuine ViewContent, not a repeat to
 * suppress.
 */
function send_view_content_event() {
  if ( ! function_exists( 'is_product' ) || ! is_product() ) {
    return;
  }

  $product = wc_get_product( get_queried_object_id() );

  if ( ! $product instanceof \WC_Product ) {
    return;
  }

  send_facebook_event( 'ViewContent', [
    'custom_data' => [
      'content_ids'   => [ $product->get_sku() ?: (string) $product->get_id() ],
      'content_type'  => 'product',
      'content_name'  => $product->get_name(),
      'value'         => (float) wc_get_price_to_display( $product ),
      'currency'      => get_woocommerce_currency(),
    ],
  ] );
} // end send_view_content_event
