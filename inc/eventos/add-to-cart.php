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

  // No dedup guard here on purpose: WooCommerce generates the same
  // $cart_item_key for the same product/variation every time (it's a hash of
  // product/variation id, not of the action itself), so keying a dedup guard
  // off it would also swallow a genuine add -> remove -> add-again of the
  // same product, which is two real AddToCart events, not a duplicate. A
  // double AJAX submit from one click still adds the product twice server
  // side (cart quantity actually goes up by 2), so two events is correct
  // there too.
  send_facebook_event( 'AddToCart', [
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
