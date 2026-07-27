<?php
/**
 * PurchaseFailed (custom event - Meta has no standard event for a payment
 * attempt that didn't go through): the counterpart to purchase.php's
 * Purchase event, fired whichever of these happens first for a given order:
 *
 * - woocommerce_order_status_failed / _cancelled / _voided: fired server-side
 *   the instant the order transitions to one of these statuses - the Wompi
 *   webhook handler (wompi-portal-de-pagos/includes/class-wompi-portal-pagos-webhook-handler.php)
 *   calls $order->update_status() with one of these the moment Wompi reports
 *   DECLINED/ERROR/VOIDED, independent of whether the customer's browser
 *   ever makes it back to the thank-you page.
 * - woocommerce_thankyou: covers the customer actually landing on
 *   template-parts/order/failed.php, for whichever gateway/scenario doesn't
 *   go through a status-transition hook above.
 *
 * 'refunded' is deliberately left out, unlike PURCHASE_EXCLUDED_ORDER_STATUSES
 * in purchase.php: a refund means the payment DID succeed at some point (the
 * opposite of a failure to track here), it's just being reversed later.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_order_status_failed', __NAMESPACE__ . '\send_purchase_failed_event' );
add_action( 'woocommerce_order_status_cancelled', __NAMESPACE__ . '\send_purchase_failed_event' );
add_action( 'woocommerce_order_status_voided', __NAMESPACE__ . '\send_purchase_failed_event' );
add_action( 'woocommerce_thankyou', __NAMESPACE__ . '\send_purchase_failed_event' );

/**
 * Order statuses treated as a failed payment attempt - same list the
 * woocommerce_valid_order_statuses_for_payment filter (inc/hooks/woocommerce.php)
 * allows "Reintentar pago"/"Cambiar método de pago" to retry.
 */
const PURCHASE_FAILED_ORDER_STATUSES = [ 'failed', 'cancelled', 'voided' ];

/**
 * @param int $order_id Order that just failed/was cancelled/voided, or reached the thank-you page in one of those statuses.
 */
function send_purchase_failed_event( $order_id ) {
  $order = wc_get_order( $order_id );

  if ( ! $order instanceof \WC_Order || ! $order->has_status( PURCHASE_FAILED_ORDER_STATUSES ) ) {
    return;
  }

  // Permanent guard, not a transient: only the first failure on a given
  // order is tracked, same reasoning as purchase.php - a customer retrying
  // with a different card after this fires isn't a second "payment failed"
  // signal worth sending again, just the same checkout attempt playing out.
  if ( get_post_meta( $order_id, '_facebook_capi_purchase_failed_sent', true ) ) {
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
      'sku'        => $product->get_sku(),
      'quantity'   => $item->get_quantity(),
      'item_price' => (float) $order->get_item_total( $item, false, false ),
    ];
  }

  if ( ! $contents ) {
    return;
  }

  $sent = send_facebook_event( facebook_capi_custom_event_name( 'facebook_capi_event_name_payment_failed', 'PaymentFailed' ), [
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
      'content_ids'    => wp_list_pluck( $contents, 'id' ),
      'content_type'   => 'product',
      'contents'       => $contents,
      'num_items'      => array_sum( wp_list_pluck( $contents, 'quantity' ) ),
      'value'          => (float) $order->get_total(),
      'currency'       => $order->get_currency(),
      'order_id'       => (string) $order_id,
      'payment_method' => $order->get_payment_method_title(),
      'order_status'   => $order->get_status(),
    ],
  ] );

  if ( $sent ) {
    update_post_meta( $order_id, '_facebook_capi_purchase_failed_sent', 1 );
  }
} // end send_purchase_failed_event
