<?php
/**
 * Purchase: fired once an order reaches the thank-you page in a non-failed
 * state - the actual conversion every other event in this folder builds
 * towards. woocommerce_thankyou fires for every order regardless of payment
 * method (COD, Wompi) since both eventually land on the same order-received
 * template (woocommerce/checkout/thankyou.php), which is also where that
 * template decides success vs. failure view - the same status list is reused
 * here so this event fires exactly when the customer sees the success page.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_thankyou', __NAMESPACE__ . '\send_purchase_event' );

/**
 * Order statuses treated as a failed/cancelled purchase, not a conversion -
 * same list woocommerce/checkout/thankyou.php uses to pick the failure view.
 */
const PURCHASE_EXCLUDED_ORDER_STATUSES = [ 'failed', 'cancelled', 'refunded', 'voided' ];

/**
 * @param int $order_id Order whose thank-you page just rendered.
 */
function send_purchase_event( $order_id ) {
  // Permanent guard, not a transient: an order only ever "converts" once, so
  // reloading the thank-you page (or the woocommerce_thankyou_{gateway} hook
  // firing right alongside it, see thankyou.php) must never resend it.
  if ( get_post_meta( $order_id, '_facebook_capi_purchase_sent', true ) ) {
    return;
  }

  $order = wc_get_order( $order_id );

  if ( ! $order instanceof \WC_Order || $order->has_status( PURCHASE_EXCLUDED_ORDER_STATUSES ) ) {
    return;
  }

  $contents = [];

  foreach ( $order->get_items() as $item ) {
    $product = $item->get_product();

    if ( ! $product instanceof \WC_Product ) {
      continue;
    }

    $contents[] = [
      'id'         => $product->get_sku() ?: (string) $product->get_id(),
      'quantity'   => $item->get_quantity(),
      'item_price' => (float) $order->get_item_total( $item, false, false ),
    ];
  }

  if ( ! $contents ) {
    return;
  }

  // Billing details straight from the order, not WC()->customer/session -
  // by the time the thank-you page loads the cart/session has already been
  // cleared, so the order itself is the only reliable source left.
  $sent = send_facebook_event( 'Purchase', [
    'event_id'    => 'purchase_' . $order_id,
    'user_data'   => [
      'em'      => facebook_capi_hash( $order->get_billing_email() ),
      'ph'      => $order->get_billing_phone() ? facebook_capi_hash( preg_replace( '/\D/', '', $order->get_billing_phone() ) ) : '',
      'fn'      => facebook_capi_hash( $order->get_billing_first_name() ),
      'ln'      => facebook_capi_hash( $order->get_billing_last_name() ),
      'ct'      => facebook_capi_hash( $order->get_billing_city() ),
      'st'      => facebook_capi_hash( $order->get_billing_state() ),
      'zp'      => facebook_capi_hash( $order->get_billing_postcode() ),
      'country' => facebook_capi_hash( $order->get_billing_country() ),
    ],
    'custom_data' => [
      'content_ids'  => wp_list_pluck( $contents, 'id' ),
      'content_type' => 'product',
      'contents'     => $contents,
      'num_items'    => array_sum( wp_list_pluck( $contents, 'quantity' ) ),
      'value'        => (float) $order->get_total(),
      'currency'     => $order->get_currency(),
      'order_id'     => (string) $order_id,
    ],
  ] );

  if ( $sent ) {
    update_post_meta( $order_id, '_facebook_capi_purchase_sent', 1 );
  }
} // end send_purchase_event
