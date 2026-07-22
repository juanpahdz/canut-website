<?php
/**
 * Product card used in the shop/category loop.
 *
 * Overrides WooCommerce's default content-product.php with the CANUT
 * design system's card-product-canut component.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product instanceof \WC_Product || ! $product->is_visible() ) {
  return;
}

$permalink = get_permalink();
$meta      = $product->get_short_description()
  ? wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 6, '' )
  : '';

/**
 * If this exact product is already sitting in the cart, render the button
 * straight into its post-add "Ver carrito" state (see the data-added-*
 * attributes/`.wc-interactive` below - modules/cart-drawer.js treats that
 * combination as "just reopen the drawer, don't add another unit" whether
 * it got there from a fresh page load like this or from clicking the button
 * itself moments ago).
 */
$already_in_cart = function_exists( 'WC' ) && WC()->cart
  && WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product->get_id() ) );

?>

<div class="card-product-canut">
  <a href="<?php echo esc_url( $permalink ); ?>" class="card-product-canut-media">
    <?php echo wp_kses_post( $product->get_image( 'large', [ 'data-object-fit' => 'cover' ] ) ); ?>

    <?php if ( $product->is_on_sale() ) : ?>
      <span class="badge-canut"><?php esc_html_e( 'Oferta', 'air-light' ); ?></span>
    <?php elseif ( $product->is_featured() ) : ?>
      <span class="badge-canut"><?php esc_html_e( 'Destacado', 'air-light' ); ?></span>
    <?php endif; ?>
  </a>

  <div class="card-product-canut-body">
    <div class="card-product-canut-info">
      <a href="<?php echo esc_url( $permalink ); ?>">
        <h3 class="card-product-canut-name"><?php echo esc_html( get_the_title() ); ?></h3>
      </a>

      <?php if ( $meta ) : ?>
        <p class="card-product-canut-meta"><?php echo esc_html( $meta ); ?></p>
      <?php endif; ?>

      <p class="card-product-canut-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
    </div>

    <?php if ( $product->is_purchasable() && $product->is_in_stock() && $product->supports( 'ajax_add_to_cart' ) ) : ?>
      <a
        href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
        data-quantity="1"
        data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
        data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
        aria-label="<?php echo esc_attr(
          $already_in_cart
            ? __( 'Ver carrito', 'air-light' )
            : sprintf(
              /* translators: %s: product name. */
              __( 'Comprar ahora: %s', 'air-light' ),
              get_the_title()
            )
        ); ?>"
        data-added-label="<?php esc_attr_e( 'Ver carrito', 'air-light' ); ?>"
        data-added-aria-label="<?php esc_attr_e( 'Ver carrito', 'air-light' ); ?>"
        class="card-product-canut-action ajax_add_to_cart add_to_cart_button<?php echo $already_in_cart ? ' wc-interactive' : ''; ?>"
      >
        <?php require get_theme_file_path( 'assets/svg/icon-shopping-bag.svg' ); ?>
        <span class="card-product-canut-action-label" data-action-label><?php echo esc_html( $already_in_cart ? __( 'Ver carrito', 'air-light' ) : __( 'Comprar ahora', 'air-light' ) ); ?></span>
      </a>
    <?php else : ?>
      <a
        href="<?php echo esc_url( $permalink ); ?>"
        aria-label="<?php echo esc_attr( sprintf(
          /* translators: %s: product name. */
          __( 'Ver %s', 'air-light' ),
          get_the_title()
        ) ); ?>"
        class="card-product-canut-action"
      >
        <?php require get_theme_file_path( 'assets/svg/icon-shopping-bag.svg' ); ?>
        <span class="card-product-canut-action-label"><?php esc_html_e( 'Ver producto', 'air-light' ); ?></span>
      </a>
    <?php endif; ?>
  </div>
</div>
