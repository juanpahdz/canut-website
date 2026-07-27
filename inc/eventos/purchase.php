<?php
/**
 * Purchase: the actual conversion every other event in this folder builds
 * towards. Fired from whichever of these happens first for a given order:
 *
 * - woocommerce_thankyou: the order-received page actually rendering - the
 *   only real "the order completed" signal for COD (contraentrega), which
 *   never calls $order->payment_complete() since nothing was actually
 *   charged at checkout time.
 * - woocommerce_payment_complete: fired by $order->payment_complete(), which
 *   the Wompi plugin's webhook handler calls the moment Wompi confirms an
 *   APPROVED transaction (class-wompi-portal-pagos-webhook-handler.php) -
 *   server-to-server, independent of the customer's browser ever making it
 *   back to the thank-you page (closed tab, dropped connection, failed
 *   redirect). Without this, a real Wompi sale could go untracked entirely.
 *
 * Both call the same send_purchase_event(), guarded by the same permanent
 * per-order flag below, so whichever fires first sends the event and the
 * other (or a reload of either) is a no-op.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_thankyou', __NAMESPACE__ . '\send_purchase_event' );
add_action( 'woocommerce_payment_complete', __NAMESPACE__ . '\send_purchase_event' );

/**
 * Order statuses treated as a failed/cancelled purchase, not a conversion -
 * same list woocommerce/checkout/thankyou.php uses to pick the failure view.
 */
const PURCHASE_EXCLUDED_ORDER_STATUSES = [ 'failed', 'cancelled', 'refunded', 'voided' ];

/**
 * @param int $order_id Order that just reached the thank-you page or had its payment marked complete.
 */
function send_purchase_event( $order_id ) {
  // Permanent guard, not a transient: an order only ever "converts" once, so
  // neither reloading the thank-you page nor the other trigger firing
  // afterwards (see the two add_action() calls above) can ever resend it.
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
      // 'id' is what Meta actually matches against your catalog (falls back
      // to the product id when there's no SKU) - 'sku' alongside it is the
      // real SKU value on its own (blank if the product has none), not the
      // id fallback, for your own reporting/matching outside of Meta.
      'id'         => $product->get_sku() ?: (string) $product->get_id(),
      'sku'        => $product->get_sku(),
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
