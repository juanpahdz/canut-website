<?php
/**
 * Front page: Section 6 - Emotional branding.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$image = $args['image'] ?? [
  'url' => get_theme_file_uri( 'assets/images/homepage/emotional-branding.jpg' ),
  'alt' => __( 'Primer plano del rostro de un golden retriever', 'air-light' ),
];
$title = $args['title'] ?? __( 'Vendemos tranquilidad', 'air-light' );

?>

<section class="home-emotional-branding">
  <div class="home-emotional-branding-media">
    <img
      src="<?php echo esc_url( $image['url'] ); ?>"
      alt="<?php echo esc_attr( $image['alt'] ); ?>"
      loading="lazy"
    >
  </div>

  <div class="wrap-canut home-emotional-branding-content">
    <h2><?php echo esc_html( $title ); ?></h2>
  </div>
</section>
