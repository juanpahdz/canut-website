<?php
/**
 * Cart drawer shell: a <dialog> opened from the header cart icon
 * (template-parts/header/actions.php, modules/cart-drawer.js).
 *
 * Included once, site-wide, from footer.php (outside the checkout branch -
 * the minimal checkout header has no cart icon to trigger it from).
 *
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

$cart_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

?>

<dialog id="cart-drawer-canut" class="cart-drawer-canut" aria-label="<?php esc_attr_e( 'Carrito de compras', 'air-light' ); ?>">

  <div class="cart-drawer-canut-header">
    <h2 class="cart-drawer-canut-title">
      <?php echo esc_html__( 'Carrito', 'air-light' ); ?>
      <span id="cart-drawer-canut-count">(<?php echo (int) $cart_count; ?>)</span>
    </h2>
    <button type="button" class="cart-drawer-canut-close" data-canut-cart-close aria-label="<?php esc_attr_e( 'Cerrar carrito', 'air-light' ); ?>">
      <?php require get_theme_file_path( 'assets/svg/icon-x.svg' ); ?>
    </button>
  </div>

  <div id="cart-drawer-canut-content">
    <?php get_template_part( 'template-parts/cart/drawer-content' ); ?>
  </div>

</dialog>
