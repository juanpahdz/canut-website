<?php
/**
 * Cart drawer (off-canvas panel, template-parts/cart/drawer.php) replacing
 * the standalone Cart page, which is unpublished - "Ver carrito" now opens
 * this instead of navigating there.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Render template-parts/cart/drawer-content.php to a string.
 *
 * Shared by the initial page load (template-parts/cart/drawer.php), the
 * add-to-cart AJAX fragment below, and the quantity-stepper AJAX handler -
 * all three must produce identical markup from the same WC()->cart state.
 *
 * @return string
 */
function cart_drawer_render_content() {
  ob_start();
  get_template_part( 'template-parts/cart/drawer-content' );

  return ob_get_clean();
} // end cart_drawer_render_content

/**
 * Keep the drawer's item count badge and content in sync with the header
 * cart icon's own count fragment (inc/hooks/woocommerce.php) whenever
 * WooCommerce's native AJAX add-to-cart fires.
 *
 * @param array $fragments Existing cart fragments.
 * @return array
 */
function cart_drawer_fragments( $fragments ) {
  $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

  $fragments['#cart-drawer-canut-content'] = '<div id="cart-drawer-canut-content">' . cart_drawer_render_content() . '</div>';
  $fragments['#cart-drawer-canut-count']   = '<span id="cart-drawer-canut-count">(' . (int) $count . ')</span>';

  return $fragments;
} // end cart_drawer_fragments
add_filter( 'woocommerce_add_to_cart_fragments', __NAMESPACE__ . '\cart_drawer_fragments' );

/**
 * AJAX: quantity stepper / remove-item clicks inside the drawer.
 *
 * There's no native WooCommerce AJAX endpoint for "change this cart item's
 * quantity" (only add-to-cart and remove-from-cart-by-URL are AJAX-ready
 * out of the box), so this re-renders the whole drawer content on every
 * click - simplest way to stay correct with stock limits, fees and the
 * automatic online-payment discount all in one place.
 */
function cart_drawer_update_qty() {
  check_ajax_referer( 'canut_cart_drawer', 'nonce' );

  $cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
  $action        = isset( $_POST['cart_action'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_action'] ) ) : '';
  $cart          = WC()->cart;

  if ( ! $cart_item_key || ! $cart || ! isset( $cart->get_cart()[ $cart_item_key ] ) ) {
    wp_send_json_error();
  }

  $cart_item = $cart->get_cart()[ $cart_item_key ];

  switch ( $action ) {
    case 'increase':
      $cart->set_quantity( $cart_item_key, $cart_item['quantity'] + 1, true );
      break;

    case 'decrease':
      $new_qty = $cart_item['quantity'] - 1;
      $cart->set_quantity( $cart_item_key, max( 0, $new_qty ), true );
      break;

    case 'remove':
      $cart->remove_cart_item( $cart_item_key );
      break;

    default:
      wp_send_json_error();
  }

  wp_send_json_success( [
    'content' => cart_drawer_render_content(),
    'count'   => $cart->get_cart_contents_count(),
  ] );
} // end cart_drawer_update_qty
add_action( 'wp_ajax_canut_cart_drawer_update', __NAMESPACE__ . '\cart_drawer_update_qty' );
add_action( 'wp_ajax_nopriv_canut_cart_drawer_update', __NAMESPACE__ . '\cart_drawer_update_qty' );

/**
 * Pass the AJAX URL + nonce modules/cart-drawer.js needs for the quantity
 * stepper. Hooked at a later priority than enqueue_theme_scripts()
 * (inc/hooks/scripts-styles.php) so the 'scripts' handle already exists.
 */
function cart_drawer_localize_script() {
  wp_localize_script( 'scripts', 'air_light_cartDrawer', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'canut_cart_drawer' ),
  ] );
} // end cart_drawer_localize_script
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\cart_drawer_localize_script', 20 );
