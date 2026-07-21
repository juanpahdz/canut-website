<?php
/**
 * Nosotros: Section 4 - CTA final.
 *
 * Fed by template-parts/blocks/nosotros-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$title               = $args['title'] ?? __( 'Únete a la excelencia CANUT', 'air-light' );
$text                = $args['text'] ?? '';
$cta_primary_label   = $args['cta_primary_label'] ?? __( 'Explorar el Catálogo', 'air-light' );
$cta_primary_url     = $args['cta_primary_url'] ?? home_url( '/tienda/' );
$cta_secondary_label = $args['cta_secondary_label'] ?? __( 'WhatsApp de Contacto', 'air-light' );
$cta_secondary_url   = $args['cta_secondary_url'] ?? '#';

?>

<section class="nosotros-cta">
  <div class="wrap-canut nosotros-cta-inner">
    <div class="nosotros-cta-content">
      <h2><?php echo esc_html( $title ); ?></h2>
      <?php if ( $text ) : ?>
        <p><?php echo esc_html( $text ); ?></p>
      <?php endif; ?>

      <div class="nosotros-cta-actions">
        <a href="<?php echo esc_url( $cta_primary_url ); ?>" class="button-canut-base button-canut-primary">
          <?php echo esc_html( $cta_primary_label ); ?>
        </a>
        <a href="<?php echo esc_url( $cta_secondary_url ); ?>" class="button-canut-base button-canut-ghost is-no-arrow">
          <?php echo esc_html( $cta_secondary_label ); ?>
        </a>
      </div>
    </div>
  </div>
</section>
