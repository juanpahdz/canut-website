<?php
/**
 * Front page: Section 8 - Warranty band.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$title     = $args['title'] ?? __( 'Compromiso CANUT: 2 años de garantía total', 'air-light' );
$text      = $args['text'] ?? __( 'Diseñados para durar toda una vida perruna.', 'air-light' );
$cta_label = $args['cta_label'] ?? __( 'Más información', 'air-light' );
$cta_url   = $args['cta_url'] ?? '#';

?>

<section class="home-warranty-band">
  <div class="wrap-canut home-warranty-band-inner">
    <div class="home-warranty-band-content">
      <h3><?php echo esc_html( $title ); ?></h3>
      <p><?php echo esc_html( $text ); ?></p>
    </div>
    <a href="<?php echo esc_url( $cta_url ); ?>" class="button-canut-base button-canut-primary">
      <?php echo esc_html( $cta_label ); ?>
    </a>
  </div>
</section>
