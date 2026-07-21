<?php
/**
 * Front page: Section 7 - Historias CANUT.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$title     = $args['title'] ?? __( 'Historias CANUT', 'air-light' );
$historias = $args['historias'] ?? [
  [
    'image'  => [ 'url' => get_theme_file_uri( 'assets/images/homepage/review-1.jpg' ), 'alt' => __( 'Camila jugando con su perro Bruno en la sala', 'air-light' ) ],
    'rating' => 5,
    'quote'  => __( 'Cambió por completo mi rutina. Viajo mucho y saber que Bruno come a sus horas me quita un peso de encima. Además, se ve hermoso en mi sala.', 'air-light' ),
    'author' => __( '— Camila & Bruno', 'air-light' ),
  ],
  [
    'image'  => [ 'url' => get_theme_file_uri( 'assets/images/homepage/review-2.jpg' ), 'alt' => __( 'Perro negro sentado junto a su comedero CANUT en la cocina', 'air-light' ) ],
    'rating' => 5,
    'quote'  => __( 'La calidad de los materiales es impresionante. No es un juguete plástico, es un mueble de lujo que realmente funciona.', 'air-light' ),
    'author' => __( '— Javier G.', 'air-light' ),
  ],
  [
    'image'  => [ 'url' => get_theme_file_uri( 'assets/images/homepage/review-3.jpg' ), 'alt' => __( 'Perro junto a su comedero CANUT y su dueña de fondo', 'air-light' ) ],
    'rating' => 5,
    'quote'  => __( 'Excelente servicio al cliente. Me ayudaron con la configuración por WhatsApp en minutos. 10/10.', 'air-light' ),
    'author' => __( '— Maria P.', 'air-light' ),
  ],
];
$archive_url = $args['archive_url'] ?? '';

?>

<section class="home-historias">
  <div class="wrap-canut home-historias-inner">
    <h2 class="home-historias-title"><?php echo esc_html( $title ); ?></h2>

    <ul class="historia-list">
      <?php foreach ( $historias as $historia ) : ?>
        <?php get_template_part( 'template-parts/historia/card', '', $historia ); ?>
      <?php endforeach; ?>
    </ul>

    <?php if ( $archive_url ) : ?>
      <div class="home-historias-cta">
        <a href="<?php echo esc_url( $archive_url ); ?>" class="button-canut-base button-canut-secondary">
          <?php esc_html_e( 'Ver todas', 'air-light' ); ?>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>
