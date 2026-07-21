<?php
/**
 * Header actions: WhatsApp CTA + cart/account icons.
 *
 * @package air-light
 */

namespace Air_Light;

?>

<div class="header-actions-canut">

  <a
    href="<?php echo esc_url( 'https://wa.me/' ); ?>"
    class="header-actions-canut-whatsapp"
    target="_blank"
    rel="noopener noreferrer"
  >
    <?php echo esc_html__( 'Escríbenos por WhatsApp', 'air-light' ); ?>
  </a>

  <div class="header-actions-canut-icons">
    <button
      type="button"
      class="header-actions-canut-icon header-actions-canut-icon-cart"
      aria-label="<?php esc_attr_e( 'Ver carrito', 'air-light' ); ?>"
      data-canut-cart-open
    >
      <?php require get_theme_file_path( 'assets/svg/icon-shopping-bag.svg' ); ?>
      <?php echo get_header_cart_count_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped in get_header_cart_count_badge() ?>
    </button>
    <a
      href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '#' ); ?>"
      class="header-actions-canut-icon header-actions-canut-icon-account"
      aria-label="<?php esc_attr_e( 'Mi cuenta', 'air-light' ); ?>"
    >
      <?php require get_theme_file_path( 'assets/svg/icon-user.svg' ); ?>
    </a>
  </div>

</div>
