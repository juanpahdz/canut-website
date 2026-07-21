<?php
/**
 * Nosotros: Section 2 - Origen (imagen + historia).
 *
 * Fed by template-parts/blocks/nosotros-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$image  = $args['image'] ?? [
  'url' => get_theme_file_uri( 'assets/images/nosotros/origen-cocina.png' ),
  'alt' => __( 'Mujer preparando alimento fresco en su cocina junto a su perro', 'air-light' ),
];
$title  = $args['title'] ?? __( 'La tranquilidad de lo bien hecho', 'air-light' );
$text_1 = $args['text_1'] ?? '';
$text_2 = $args['text_2'] ?? '';
$quote  = $args['quote'] ?? '';

?>

<section class="nosotros-origen">
  <div class="wrap-canut nosotros-origen-inner">
    <div class="nosotros-origen-media">
      <img
        src="<?php echo esc_url( $image['url'] ); ?>"
        alt="<?php echo esc_attr( $image['alt'] ); ?>"
        loading="lazy"
      >
    </div>

    <div class="nosotros-origen-content">
      <h2 class="nosotros-origen-title"><?php echo esc_html( $title ); ?></h2>

      <?php if ( $text_1 ) : ?>
        <p class="nosotros-origen-text"><?php echo esc_html( $text_1 ); ?></p>
      <?php endif; ?>

      <?php if ( $text_2 ) : ?>
        <p class="nosotros-origen-text nosotros-origen-text-small"><?php echo esc_html( $text_2 ); ?></p>
      <?php endif; ?>

      <?php if ( $quote ) : ?>
        <blockquote class="nosotros-origen-quote">
          <p>&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
        </blockquote>
      <?php endif; ?>
    </div>
  </div>
</section>
