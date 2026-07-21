<?php
/**
 * Front page: Section 9 - Final CTA.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$image     = $args['image'] ?? [
  'url' => get_theme_file_uri( 'assets/images/homepage/final-cta.jpg' ),
  'alt' => __( 'Línea completa de productos CANUT sobre un mueble de madera', 'air-light' ),
];
$title     = $args['title'] ?? __( 'El futuro del cuidado está aquí', 'air-light' );
$cta_label = $args['cta_label'] ?? __( 'Ver comederos', 'air-light' );
$cta_url   = $args['cta_url'] ?? home_url( '/tienda/' );

?>

<section class="home-final-cta">
  <div class="home-final-cta-media">
    <img
      src="<?php echo esc_url( $image['url'] ); ?>"
      alt="<?php echo esc_attr( $image['alt'] ); ?>"
      loading="lazy"
    >
  </div>

  <div class="wrap-canut home-final-cta-content">
    <h2><?php echo esc_html( $title ); ?></h2>
    <a href="<?php echo esc_url( $cta_url ); ?>" class="button-canut-base button-canut-light">
      <?php echo esc_html( $cta_label ); ?>
    </a>
  </div>
</section>
