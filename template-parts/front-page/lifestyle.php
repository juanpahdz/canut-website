<?php
/**
 * Front page: Section 3 - Full-width lifestyle image.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$image = $args['image'] ?? [
  'url' => get_theme_file_uri( 'assets/images/homepage/lifestyle-living-room.jpg' ),
  'alt' => __( 'Sala de estar acogedora con un comedero CANUT junto a la mesa de centro', 'air-light' ),
];
$title = $args['title'] ?? __( 'No estás en casa a la hora de comer, pero tu perro sí.', 'air-light' );
$text  = $args['text'] ?? __( 'Programación inteligente para que nunca falte su plato.', 'air-light' );

?>

<section class="home-lifestyle">
  <img
    class="home-lifestyle-media"
    src="<?php echo esc_url( $image['url'] ); ?>"
    alt="<?php echo esc_attr( $image['alt'] ); ?>"
    loading="lazy"
  >

  <div class="home-lifestyle-card">
    <h2 class="home-lifestyle-card-title"><?php echo esc_html( $title ); ?></h2>
    <p class="home-lifestyle-card-text"><?php echo esc_html( $text ); ?></p>
  </div>
</section>
