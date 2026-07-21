<?php
/**
 * Contacto: Section 1 - Hero.
 *
 * Fed by template-parts/blocks/contacto-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$title = $args['title'] ?? __( 'Contacto', 'air-light' );
$text  = $args['text'] ?? __( 'Estamos aquí para acompañarte a ti y a tu mascota. Nuestro equipo de expertos está listo para resolver cualquier duda.', 'air-light' );

?>

<header class="contacto-hero">
  <div class="wrap-canut contacto-hero-inner">
    <h1 class="contacto-hero-title"><?php echo esc_html( $title ); ?></h1>
    <?php if ( $text ) : ?>
      <p class="contacto-hero-text"><?php echo esc_html( $text ); ?></p>
    <?php endif; ?>
  </div>
</header>
