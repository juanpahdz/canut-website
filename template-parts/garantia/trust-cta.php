<?php
/**
 * Garantía: Section 4 - Tarjetas de confianza + CTA final (WhatsApp).
 *
 * Fed by template-parts/blocks/garantia-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$signals          = $args['signals'] ?? [];
$cta_title        = $args['cta_title'] ?? __( '¿Tienes dudas sobre tu proceso?', 'air-light' );
$cta_text         = $args['cta_text'] ?? __( 'Nuestro equipo de soporte está listo para ayudarte vía WhatsApp.', 'air-light' );
$cta_button_label = $args['cta_button_label'] ?? __( 'Escríbenos por WhatsApp', 'air-light' );
$cta_button_url   = $args['cta_button_url'] ?? 'https://wa.me/';

?>

<section class="garantia-trust-wrap">
  <div class="wrap-canut">
    <div class="garantia-trust-inner">

      <div class="garantia-trust-grid">
        <?php foreach ( $signals as $signal ) : ?>
          <div class="garantia-trust-card is-<?php echo esc_attr( $signal['variant'] ); ?>">
            <span class="garantia-trust-card-icon">
              <?php require get_theme_file_path( 'assets/svg/icon-' . $signal['icon'] . '.svg' ); ?>
            </span>
            <h3 class="garantia-trust-card-title"><?php echo esc_html( $signal['title'] ); ?></h3>
            <p class="garantia-trust-card-text"><?php echo esc_html( $signal['text'] ); ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="garantia-cta">
        <div class="garantia-cta-content">
          <p class="garantia-cta-title"><?php echo esc_html( $cta_title ); ?></p>
          <p class="garantia-cta-text"><?php echo esc_html( $cta_text ); ?></p>
        </div>
        <a class="garantia-cta-button" href="<?php echo esc_url( $cta_button_url ); ?>" target="_blank" rel="noopener noreferrer">
          <?php require get_theme_file_path( 'assets/svg/icon-whatsapp.svg' ); ?>
          <?php echo esc_html( $cta_button_label ); ?>
        </a>
      </div>

    </div>
  </div>
</section>
