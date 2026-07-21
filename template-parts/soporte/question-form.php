<?php
/**
 * Soporte: "Crea tu pregunta" form.
 *
 * Native form, posted to admin-post.php and handled by soporte_form_submit()
 * in inc/hooks/soporte.php - same convention as the Contacto page form
 * (template-parts/contacto/form.php), since no form plugin is active on
 * this site. Creates a pending Pregunta_Soporte post.
 *
 * Adds an email field beyond the original Figma design (file kt0CQUzjzqKYQOklRo72EA,
 * node 87:612), confirmed with the client so an editor can follow up with
 * whoever asked - not guessed.
 *
 * After submit the visitor is redirected back to this same page with
 * ?soporte=exito|error, read below to show a status banner.
 *
 * @param array $args {
 *   @type WP_Term[] $terms Categoria_Ayuda terms for the "Tema" select.
 * }
 *
 * @package air-light
 */

namespace Air_Light;

$terms = $args['terms'] ?? [];

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, only decides which banner to show; the actual submit is nonce-verified in soporte_form_submit().
$status = isset( $_GET['soporte'] ) ? sanitize_key( wp_unslash( $_GET['soporte'] ) ) : '';

?>

<div class="soporte-form-card">
  <div class="soporte-form-heading">
    <h2><?php esc_html_e( 'Crea tu pregunta', 'air-light' ); ?></h2>
    <p><?php esc_html_e( '¿En qué podemos ayudarte hoy? Nuestros expertos y la comunidad te responderán pronto.', 'air-light' ); ?></p>
  </div>

  <?php if ( 'exito' === $status ) : ?>
    <div class="banner-canut is-mint" role="status">
      <?php require get_theme_file_path( 'assets/svg/icon-check.svg' ); ?>
      <p class="banner-canut-text"><?php esc_html_e( '¡Gracias! Tu pregunta fue enviada y la revisaremos pronto.', 'air-light' ); ?></p>
    </div>
  <?php elseif ( 'error' === $status ) : ?>
    <div class="banner-canut is-error" role="alert">
      <?php require get_theme_file_path( 'assets/svg/icon-info.svg' ); ?>
      <p class="banner-canut-text"><?php esc_html_e( 'Completa tu nombre, email, tema y pregunta antes de enviar.', 'air-light' ); ?></p>
    </div>
  <?php endif; ?>

  <form class="soporte-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="canut_soporte_submit">
    <input type="hidden" name="canut_soporte_redirect" value="<?php echo esc_url( get_permalink() ); ?>">
    <?php wp_nonce_field( 'canut_soporte_form', 'canut_soporte_nonce' ); ?>

    <p class="soporte-form-hp">
      <label for="canut_soporte_hp"><?php esc_html_e( 'Dejar en blanco', 'air-light' ); ?></label>
      <input type="text" id="canut_soporte_hp" name="canut_soporte_hp" tabindex="-1" autocomplete="off">
    </p>

    <div class="soporte-form-row">
      <div class="soporte-form-field">
        <label class="form-canut-label" for="canut_soporte_nombre"><?php esc_html_e( 'Nombre completo', 'air-light' ); ?></label>
        <input class="input-canut" type="text" id="canut_soporte_nombre" name="canut_soporte_nombre" placeholder="<?php echo esc_attr__( 'Ej. Alejandra Valdés', 'air-light' ); ?>" autocomplete="name" required>
      </div>
      <div class="soporte-form-field">
        <label class="form-canut-label" for="canut_soporte_email"><?php esc_html_e( 'Email', 'air-light' ); ?></label>
        <input class="input-canut" type="email" id="canut_soporte_email" name="canut_soporte_email" placeholder="hola@ejemplo.com" autocomplete="email" required>
      </div>
    </div>

    <div class="soporte-form-field">
      <label class="form-canut-label" for="canut_soporte_tema"><?php esc_html_e( 'Tema', 'air-light' ); ?></label>
      <div class="select-canut-wrap">
        <select class="select-canut" id="canut_soporte_tema" name="canut_soporte_tema" required>
          <option value=""><?php esc_html_e( 'Elige un tema', 'air-light' ); ?></option>
          <?php foreach ( $terms as $term ) : ?>
            <option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="soporte-form-field">
      <label class="form-canut-label" for="canut_soporte_pregunta"><?php esc_html_e( 'Tu pregunta', 'air-light' ); ?></label>
      <textarea class="textarea-canut" id="canut_soporte_pregunta" name="canut_soporte_pregunta" rows="4" placeholder="<?php echo esc_attr__( 'Describe tu duda con el mayor detalle posible...', 'air-light' ); ?>" required></textarea>
    </div>

    <button type="submit" class="button-canut-base button-canut-primary is-no-arrow">
      <?php esc_html_e( 'Publicar pregunta', 'air-light' ); ?>
    </button>
  </form>
</div>
