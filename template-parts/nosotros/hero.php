<?php
/**
 * Nosotros: Section 1 - Hero.
 *
 * Fed by template-parts/blocks/nosotros-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$image   = $args['image'] ?? [
  'url' => get_theme_file_uri( 'assets/images/nosotros/hero-manifesto.png' ),
  'alt' => __( 'Perro descansando junto a una cama CANUT en una sala de estar', 'air-light' ),
];
$eyebrow = $args['eyebrow'] ?? __( 'Nuestro manifiesto', 'air-light' );
$title   = $args['title'] ?? __( 'Por qué existe CANUT', 'air-light' );
$text    = $args['text'] ?? __( 'Nacimos de una obsesión silenciosa: la tranquilidad de saber, sin ninguna duda, que ellos están recibiendo lo mejor. No es solo comida; es el compromiso con su longevidad.', 'air-light' );

?>

<section class="nosotros-hero">
  <div class="nosotros-hero-media">
    <img
      src="<?php echo esc_url( $image['url'] ); ?>"
      alt="<?php echo esc_attr( $image['alt'] ); ?>"
      loading="eager"
      fetchpriority="high"
    >
  </div>

  <div class="wrap-canut nosotros-hero-content">
    <span class="nosotros-hero-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
    <h1 class="nosotros-hero-title"><?php echo esc_html( $title ); ?></h1>
    <p class="nosotros-hero-text"><?php echo esc_html( $text ); ?></p>
  </div>
</section>
