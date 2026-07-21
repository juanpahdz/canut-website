<?php
/**
 * Front page: Section 4 - Featured product.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args, which sources
 * image/title/description/price straight from the WooCommerce product
 * selected in ACF (falling back to the store's only product). Falls back to
 * the original CANUT design copy when included without $args, e.g. when
 * WooCommerce is inactive or the store has no products yet.
 *
 * @package air-light
 */

namespace Air_Light;

$image               = $args['image'] ?? [
  'url' => get_theme_file_uri( 'assets/images/homepage/featured-product.jpg' ),
  'alt' => __( 'Perro junto al Heritage Smart Feeder en una sala de estar', 'air-light' ),
];
$eyebrow             = $args['eyebrow'] ?? __( 'Edición limitada', 'air-light' );
$title               = $args['title'] ?? __( 'Heritage Smart Feeder', 'air-light' );
$description         = $args['description'] ?? __( 'Nuestra pieza maestra. Combina maderas nobles tratadas a mano con nuestra tecnología de dispensación de precisión.', 'air-light' );
$price               = $args['price'] ?? '$1.250.000 COP';
$cta_primary_label   = $args['cta_primary_label'] ?? __( 'Lo quiero ya', 'air-light' );
$cta_primary_url     = $args['cta_primary_url'] ?? home_url( '/tienda/' );
$cta_secondary_label = $args['cta_secondary_label'] ?? __( 'Especificaciones', 'air-light' );
$cta_secondary_url   = $args['cta_secondary_url'] ?? '#';

?>

<section id="featured-product" class="home-featured-product">
  <div class="wrap-canut home-featured-product-inner">

    <div class="home-featured-product-media">
      <img
        src="<?php echo esc_url( $image['url'] ); ?>"
        alt="<?php echo esc_attr( $image['alt'] ); ?>"
        loading="lazy"
      >
    </div>

    <div class="home-featured-product-content">
      <span class="home-featured-product-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
      <h2 class="home-featured-product-title"><?php echo esc_html( $title ); ?></h2>
      <p class="home-featured-product-description"><?php echo esc_html( $description ); ?></p>
      <p class="home-featured-product-price"><?php echo wp_kses_post( $price ); ?></p>
      <div class="home-featured-product-actions">
        <a href="<?php echo esc_url( $cta_primary_url ); ?>" class="button-canut-base button-canut-checkout">
          <?php echo esc_html( $cta_primary_label ); ?>
        </a>
        <a href="<?php echo esc_url( $cta_secondary_url ); ?>" class="button-canut-base button-canut-secondary">
          <?php echo esc_html( $cta_secondary_label ); ?>
        </a>
      </div>
    </div>

  </div>
</section>
