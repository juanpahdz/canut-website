<?php
/**
 * AddToCart: fired whenever a product is added to the cart - the main event
 * cart-abandonment remarketing audiences are built on. Hooks WooCommerce's
 * own woocommerce_add_to_cart action, which fires for every add path (the
 * product page's AJAX button, the shop grid's quick-add, buy-now links) since
 * they're all really just calls into WC_Cart::add_to_cart() underneath - one
 * hook covers all of them.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_add_to_cart', __NAMESPACE__ . '\send_add_to_cart_event', 10, 6 );

/**
 * @param string $cart_item_key WooCommerce's generated cart item key.
 * @param int    $product_id    Product added.
 * @param int    $quantity      Quantity added in this action.
 * @param int    $variation_id  Variation id, 0 for a simple product.
 */
function send_add_to_cart_event( $cart_item_key, $product_id, $quantity, $variation_id ) {
  $product = wc_get_product( $variation_id ?: $product_id );

  if ( ! $product instanceof \WC_Product ) {
    return;
  }

  send_facebook_event( 'AddToCart', [
    // Short-lived guard against the hook firing twice for the same click
    // (double AJAX submit) - not meant to block a genuine second add of the
    // same product a few seconds later.
    'dedup_key'   => $cart_item_key . '|' . $quantity,
    'dedup_ttl'   => 10,
    'custom_data' => [
      'content_ids'  => [ $product->get_sku() ?: (string) $product->get_id() ],
      'content_type' => 'product',
      'content_name' => $product->get_name(),
      'contents'     => [
        [
          'id'         => $product->get_sku() ?: (string) $product->get_id(),
          'quantity'   => $quantity,
          'item_price' => (float) wc_get_price_to_display( $product ),
        ],
      ],
      'value'        => (float) wc_get_price_to_display( $product ) * $quantity,
      'currency'     => get_woocommerce_currency(),
    ],
  ] );
} // end send_add_to_cart_event
