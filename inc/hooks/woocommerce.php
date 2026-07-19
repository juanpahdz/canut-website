<?php
/**
 * WooCommerce related hooks.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Show 3 products per row in the shop/archive loop
 *
 * The content wrapper itself (the theme's <main> markup) is handled by
 * woocommerce/global/wrapper-start.php and wrapper-end.php, not a hook.
 */
function woocommerce_loop_columns() {
  return 3;
} // end woocommerce_loop_columns
