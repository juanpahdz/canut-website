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
 * Sends ViewContent for the product being viewed. Deduped per (session,
 * product) for 30 minutes, so refreshing/going back to the same product page
 * doesn't spam the Conversions API with what is, from Facebook's audience
 * building perspective, the same "just looked at this product" signal.
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
    'dedup_key'   => facebook_capi_session_id() . '|' . $product->get_id(),
    'dedup_ttl'   => 30 * MINUTE_IN_SECONDS,
    'custom_data' => [
      'content_ids'   => [ $product->get_sku() ?: (string) $product->get_id() ],
      'content_type'  => 'product',
      'content_name'  => $product->get_name(),
      'value'         => (float) wc_get_price_to_display( $product ),
      'currency'      => get_woocommerce_currency(),
    ],
  ] );
} // end send_view_content_event
