<?php
/**
 * Content wrapper start.
 *
 * Overridden to open the theme's own <main> markup (matching page.php and
 * index.php) instead of WooCommerce's default per-theme-slug switch.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

echo '<main class="site-main woocommerce-main">';
