<?php
/**
 * Front page: Section 1 - Hero.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$image    = $args['image'] ?? [
  'url' => get_theme_file_uri( 'assets/images/homepage/hero-dog-feeder.jpg' ),
  'alt' => __( 'Perro descansando junto a un comedero inteligente CANUT', 'air-light' ),
];
$title    = $args['title'] ?? __( 'Tu perro siempre come a tiempo', 'air-light' );
$subtitle = $args['subtitle'] ?? __( 'Tecnología y diseño artesanal creados para el bienestar de quien más te quiere.', 'air-light' );
$cta_label = $args['cta_label'] ?? __( 'Comprar ahora', 'air-light' );
$cta_url   = $args['cta_url'] ?? '#featured-product';

?>

<section class="home-hero">
  <div class="home-hero-media">
    <img
      src="<?php echo esc_url( $image['url'] ); ?>"
      alt="<?php echo esc_attr( $image['alt'] ); ?>"
      loading="eager"
      fetchpriority="high"
    >
  </div>

  <div class="wrap-canut home-hero-content">
    <h1 class="home-hero-title"><?php echo esc_html( $title ); ?></h1>
    <p class="home-hero-subtitle"><?php echo esc_html( $subtitle ); ?></p>
    <a href="<?php echo esc_url( $cta_url ); ?>" class="button-canut-base button-canut-ghost home-hero-cta">
      <?php echo esc_html( $cta_label ); ?>
    </a>
  </div>
</section>
