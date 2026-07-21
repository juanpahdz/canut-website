<?php
/**
 * Forms related hooks.
 *
 * @package air-light
 */

namespace Air_Light;

// Always set Output CSS setting to No. We want to use our own _gravity-forms.scss
function dequeue_gf_stylesheets() {
  wp_dequeue_style( 'gforms_reset_css' );
  wp_dequeue_style( 'gforms_datepicker_css' );
  wp_dequeue_style( 'gforms_formsmain_css' );
  wp_dequeue_style( 'gforms_ready_class_css' );
  wp_dequeue_style( 'gforms_browsers_css' );
}

// Disable printing Gravity Forms js straight after <head> (invalid HTML)
add_filter( 'gform_force_hooks_js_output', '__return_false' );

/**
 * Handles the native Contacto page form (template-parts/contacto/form.php).
 *
 * No form plugin (Gravity Forms or otherwise) is active on this site, so
 * this page uses a plain admin-post.php submit + wp_mail() instead. If
 * Gravity Forms is installed later, replace the form markup with a GF form
 * and remove this handler - the dequeue/JS hooks above already anticipate
 * that.
 */
function contact_form_submit() {
  $redirect_url = ! empty( $_POST['canut_contact_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['canut_contact_redirect'] ) ) : home_url( '/' );

  if ( ! isset( $_POST['canut_contact_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['canut_contact_nonce'] ), 'canut_contact_form' ) ) {
    wp_safe_redirect( add_query_arg( 'contacto', 'error', $redirect_url ) );
    exit;
  }

  // Honeypot: hidden from real visitors via CSS, only bots fill it in.
  if ( ! empty( $_POST['canut_contact_hp'] ) ) {
    wp_safe_redirect( $redirect_url );
    exit;
  }

  $name    = isset( $_POST['canut_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['canut_contact_name'] ) ) : '';
  $email   = isset( $_POST['canut_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['canut_contact_email'] ) ) : '';
  $phone   = isset( $_POST['canut_contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['canut_contact_phone'] ) ) : '';
  $message = isset( $_POST['canut_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['canut_contact_message'] ) ) : '';
  $to      = isset( $_POST['canut_contact_to'] ) ? sanitize_email( wp_unslash( $_POST['canut_contact_to'] ) ) : '';
  $to      = is_email( $to ) ? $to : get_option( 'admin_email' );

  if ( ! $name || ! is_email( $email ) || ! $message ) {
    wp_safe_redirect( add_query_arg( 'contacto', 'error', $redirect_url ) );
    exit;
  }

  // translators: %s: visitor's name.
  $subject = sprintf( __( '[%1$s] Nuevo mensaje de contacto de %2$s', 'air-light' ), get_bloginfo( 'name' ), $name );
  $body    = implode( "\n", [
    __( 'Nombre:', 'air-light' ) . ' ' . $name,
    __( 'Email:', 'air-light' ) . ' ' . $email,
    __( 'Teléfono:', 'air-light' ) . ' ' . ( $phone ?: '-' ),
    '',
    __( 'Mensaje:', 'air-light' ),
    $message,
  ] );
  $headers = [ 'Reply-To: ' . $name . ' <' . $email . '>' ];

  $sent = wp_mail( $to, $subject, $body, $headers );

  wp_safe_redirect( add_query_arg( 'contacto', $sent ? 'exito' : 'error', $redirect_url ) );
  exit;
} // end contact_form_submit
add_action( 'admin_post_canut_contact_form', __NAMESPACE__ . '\contact_form_submit' );
add_action( 'admin_post_nopriv_canut_contact_form', __NAMESPACE__ . '\contact_form_submit' );
