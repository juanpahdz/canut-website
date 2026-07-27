<?php
/**
 * InitiatePayment (custom event - Meta has no standard event for "order
 * created, about to enter payment processing"): the checkout wizard's 4th
 * and final moment - the customer clicked "Finalizar compra" and WooCommerce
 * just created the order, right before it hands off to the chosen payment
 * gateway (Wompi) or marks it processing (contraentrega). Configurable event
 * name, see Ajustes > Facebook Pixel.
 *
 * Distinct from AddPaymentInfo (add-payment-info.php, fires earlier - a
 * payment method merely selected, before the order exists) and Purchase
 * (purchase.php, fires once the order actually completes on the thank-you
 * page).
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_checkout_order_processed', __NAMESPACE__ . '\send_initiate_payment_event', 10, 3 );

/**
 * @param int       $order_id    Order just created.
 * @param array     $posted_data Unused - $order already has everything posted.
 * @param \WC_Order $order       Order just created.
 */
function send_initiate_payment_event( $order_id, $posted_data, $order ) {
  // Permanent guard: an order is only ever "just created" once. Kept anyway
  // as cheap insurance against a plugin hooking woocommerce_checkout_order_processed
  // more than once for the same order, same reasoning as purchase.php.
  if ( get_post_meta( $order_id, '_facebook_capi_initiate_payment_sent', true ) ) {
    return;
  }

  if ( ! $order instanceof \WC_Order ) {
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

  $sent = send_facebook_event( facebook_capi_custom_event_name( 'facebook_capi_event_name_payment_initiated', 'InitiatePayment' ), [
    'user_data'   => [
      'em' => facebook_capi_hash( $order->get_billing_email() ),
      'ph' => $order->get_billing_phone() ? facebook_capi_hash( preg_replace( '/\D/', '', $order->get_billing_phone() ) ) : '',
    ],
    'custom_data' => [
      'content_ids'     => wp_list_pluck( $contents, 'id' ),
      'content_type'    => 'product',
      'contents'        => $contents,
      'value'           => (float) $order->get_total(),
      'currency'        => $order->get_currency(),
      'order_id'        => (string) $order_id,
      'payment_method'  => $order->get_payment_method_title(),
    ],
  ] );

  if ( $sent ) {
    update_post_meta( $order_id, '_facebook_capi_initiate_payment_sent', 1 );
  }
} // end send_initiate_payment_event
