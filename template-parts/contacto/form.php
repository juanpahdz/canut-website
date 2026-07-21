<?php
/**
 * Contacto: Section 2 - Formulario (left column).
 *
 * Native WordPress form, posted to admin-post.php and handled by
 * contact_form_submit() in inc/hooks/forms.php (wp_mail to the ACF-editable
 * recipient address). No form plugin is active on this site, see that
 * handler's docblock for why this isn't Gravity Forms.
 *
 * After submit the visitor is redirected back to this same page with
 * ?contacto=exito|error, read below to show a status banner - this is why
 * the block that renders this (contacto-canut) has 'prevent_cache' set.
 *
 * Fed by template-parts/blocks/contacto-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$title            = $args['title'] ?? __( 'Envíanos un mensaje', 'air-light' );
$button_label     = $args['button_label'] ?? __( 'Enviar mensaje', 'air-light' );
$success_message  = $args['success_message'] ?? __( '¡Gracias! Tu mensaje fue enviado, te responderemos pronto.', 'air-light' );
$error_message    = $args['error_message'] ?? __( 'Ocurrió un error al enviar tu mensaje. Intenta de nuevo o escríbenos por WhatsApp.', 'air-light' );
$recipient_email  = $args['recipient_email'] ?? get_option( 'admin_email' );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, only decides which banner to show; the actual submit is nonce-verified in contact_form_submit().
$status = isset( $_GET['contacto'] ) ? sanitize_key( wp_unslash( $_GET['contacto'] ) ) : '';

?>

<div class="contacto-form-card">
  <h2 class="contacto-form-title"><?php echo esc_html( $title ); ?></h2>

  <?php if ( 'exito' === $status ) : ?>
    <div class="banner-canut is-mint" role="status">
      <?php require get_theme_file_path( 'assets/svg/icon-check.svg' ); ?>
      <p class="banner-canut-text"><?php echo esc_html( $success_message ); ?></p>
    </div>
  <?php elseif ( 'error' === $status ) : ?>
    <div class="banner-canut is-error" role="alert">
      <?php require get_theme_file_path( 'assets/svg/icon-info.svg' ); ?>
      <p class="banner-canut-text"><?php echo esc_html( $error_message ); ?></p>
    </div>
  <?php endif; ?>

  <form class="contacto-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="canut_contact_form">
    <input type="hidden" name="canut_contact_redirect" value="<?php echo esc_url( get_permalink() ); ?>">
    <input type="hidden" name="canut_contact_to" value="<?php echo esc_attr( $recipient_email ); ?>">
    <?php wp_nonce_field( 'canut_contact_form', 'canut_contact_nonce' ); ?>

    <p class="contacto-form-hp">
      <label for="canut_contact_hp"><?php esc_html_e( 'Dejar en blanco', 'air-light' ); ?></label>
      <input type="text" id="canut_contact_hp" name="canut_contact_hp" tabindex="-1" autocomplete="off">
    </p>

    <div class="contacto-form-row">
      <div class="contacto-form-field">
        <label class="form-canut-label" for="canut_contact_name"><?php esc_html_e( 'Nombre', 'air-light' ); ?></label>
        <input class="input-canut" type="text" id="canut_contact_name" name="canut_contact_name" placeholder="<?php esc_attr_e( 'Tu nombre completo', 'air-light' ); ?>" autocomplete="name" required>
      </div>
      <div class="contacto-form-field">
        <label class="form-canut-label" for="canut_contact_email"><?php esc_html_e( 'Email', 'air-light' ); ?></label>
        <input class="input-canut" type="email" id="canut_contact_email" name="canut_contact_email" placeholder="hola@ejemplo.com" autocomplete="email" required>
      </div>
    </div>

    <div class="contacto-form-field">
      <label class="form-canut-label" for="canut_contact_phone"><?php esc_html_e( 'Teléfono', 'air-light' ); ?></label>
      <input class="input-canut" type="tel" id="canut_contact_phone" name="canut_contact_phone" placeholder="+57 300 000 0000" autocomplete="tel">
    </div>

    <div class="contacto-form-field">
      <label class="form-canut-label" for="canut_contact_message"><?php esc_html_e( 'Mensaje', 'air-light' ); ?></label>
      <textarea class="textarea-canut" id="canut_contact_message" name="canut_contact_message" rows="5" placeholder="<?php esc_attr_e( '¿En qué podemos ayudarte?', 'air-light' ); ?>" required></textarea>
    </div>

    <button type="submit" class="button-canut-base button-canut-primary is-full-width is-no-arrow">
      <?php echo esc_html( $button_label ); ?>
    </button>
  </form>
</div>
