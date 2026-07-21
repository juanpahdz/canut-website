<?php
/**
 * Soporte (page-soporte.php): community question form + approval workflow.
 *
 * The "Crea tu pregunta" form (template-parts/soporte/question-form.php) is
 * a plain admin-post.php submit + redirect, same convention as the Contacto
 * page form (template-parts/contacto/form.php / contact_form_submit() in
 * inc/hooks/forms.php) since no form plugin is active on this site.
 * soporte_form_submit() below creates a `pregunta_soporte` post with
 * post_status `pending` - title is the question, `nombre_remitente`/
 * `email_remitente` (ACF, inc/acf-fields/pregunta-soporte.php) hold the
 * submitter's contact details, and the chosen "Tema" is saved as a
 * `categoria_ayuda` term (the same taxonomy used by the Centro de ayuda
 * FAQ, inc/hooks/help-center.php).
 *
 * Approval is just the native WordPress editorial flow: an editor opens the
 * pending post in wp-admin, writes the answer into the native editor, and
 * clicking "Publicar" both saves that content and moves the status to
 * `publish` in one request. `maybe_sync_pregunta_soporte_to_recurso_ayuda()`
 * below catches that transition and mirrors the question into a new
 * `Recurso_Ayuda` post - the CPT that actually feeds the Centro de ayuda
 * FAQ listing (page-centro-de-ayuda.php only queries published resources) -
 * carrying over the same categoria_ayuda term. The original pregunta_soporte
 * post is left in place as the moderation record, linked to its mirror via
 * `_recurso_ayuda_id` postmeta so it's only ever synced once.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Handles the native Soporte page form (template-parts/soporte/question-form.php).
 */
function soporte_form_submit() {
  $redirect_url = ! empty( $_POST['canut_soporte_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['canut_soporte_redirect'] ) ) : home_url( '/' );

  if ( ! isset( $_POST['canut_soporte_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['canut_soporte_nonce'] ), 'canut_soporte_form' ) ) {
    wp_safe_redirect( add_query_arg( 'soporte', 'error', $redirect_url ) );
    exit;
  }

  // Honeypot: hidden from real visitors via CSS, only bots fill it in.
  if ( ! empty( $_POST['canut_soporte_hp'] ) ) {
    wp_safe_redirect( $redirect_url );
    exit;
  }

  $nombre   = isset( $_POST['canut_soporte_nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['canut_soporte_nombre'] ) ) : '';
  $email    = isset( $_POST['canut_soporte_email'] ) ? sanitize_email( wp_unslash( $_POST['canut_soporte_email'] ) ) : '';
  $tema_id  = isset( $_POST['canut_soporte_tema'] ) ? absint( $_POST['canut_soporte_tema'] ) : 0;
  $pregunta = isset( $_POST['canut_soporte_pregunta'] ) ? sanitize_textarea_field( wp_unslash( $_POST['canut_soporte_pregunta'] ) ) : '';

  if ( ! $nombre || ! is_email( $email ) || ! $tema_id || ! term_exists( $tema_id, 'categoria_ayuda' ) || ! $pregunta ) {
    wp_safe_redirect( add_query_arg( 'soporte', 'error', $redirect_url ) );
    exit;
  }

  $post_id = wp_insert_post( [
    'post_type'   => 'pregunta_soporte',
    'post_status' => 'pending',
    'post_title'  => $pregunta,
  ], true );

  if ( is_wp_error( $post_id ) ) {
    wp_safe_redirect( add_query_arg( 'soporte', 'error', $redirect_url ) );
    exit;
  }

  update_field( 'nombre_remitente', $nombre, $post_id );
  update_field( 'email_remitente', $email, $post_id );
  wp_set_object_terms( $post_id, [ $tema_id ], 'categoria_ayuda' );

  soporte_notify_admin_of_new_question( $post_id );

  wp_safe_redirect( add_query_arg( 'soporte', 'exito', $redirect_url ) );
  exit;
} // end soporte_form_submit
add_action( 'admin_post_canut_soporte_submit', __NAMESPACE__ . '\soporte_form_submit' );
add_action( 'admin_post_nopriv_canut_soporte_submit', __NAMESPACE__ . '\soporte_form_submit' );

/**
 * Two-letter initials for the "Últimas preguntas" sidebar avatars
 * (template-parts/soporte/recent-questions.php), e.g. "Alejandra Valdés" -> "AV".
 *
 * @param string $name Submitter's full name.
 * @return string
 */
function soporte_get_initials( $name ) {
  $words = preg_split( '/\s+/', trim( $name ) );
  $words = array_filter( $words );

  if ( ! $words ) {
    return '';
  }

  $initials = count( $words ) > 1
    ? mb_substr( reset( $words ), 0, 1 ) . mb_substr( end( $words ), 0, 1 )
    : mb_substr( reset( $words ), 0, 2 );

  return mb_strtoupper( $initials );
} // end soporte_get_initials

/**
 * Email the site admin so a new pending question doesn't sit unnoticed.
 *
 * @param int $post_id The new pregunta_soporte post ID.
 */
function soporte_notify_admin_of_new_question( $post_id ) {
  $edit_link = get_edit_post_link( $post_id, 'raw' );

  wp_mail(
    get_option( 'admin_email' ),
    __( 'Nueva pregunta de soporte pendiente de revisión', 'air-light' ),
    sprintf(
      /* translators: %s: link to review/approve the question in wp-admin */
      __( "Se envió una nueva pregunta a través de la página de Soporte.\n\nRevísala y publícala para que aparezca en el Centro de ayuda:\n%s", 'air-light' ),
      $edit_link
    )
  );
} // end soporte_notify_admin_of_new_question

/**
 * Mirror an approved pregunta_soporte into a published Recurso_Ayuda post,
 * so it starts showing up on the Centro de ayuda FAQ listing.
 *
 * Runs both on the actual pending -> publish transition, and as a fallback
 * on later saves (save_post_pregunta_soporte): if an editor clicks
 * "Publicar" before writing an answer, the transition alone won't have
 * content to sync yet - this re-checks on every subsequent save, since
 * `transition_post_status` only fires when the status itself changes.
 *
 * @param int|\WP_Post $post Post ID or object.
 */
function maybe_sync_pregunta_soporte_to_recurso_ayuda( $post ) {
  $post = get_post( $post );

  if ( ! $post instanceof \WP_Post || 'pregunta_soporte' !== $post->post_type ) {
    return;
  }

  if ( 'publish' !== $post->post_status ) {
    return;
  }

  // Already mirrored - never create a second Recurso_Ayuda for the same question.
  if ( get_post_meta( $post->ID, '_recurso_ayuda_id', true ) ) {
    return;
  }

  $respuesta = trim( $post->post_content );

  if ( '' === $respuesta ) {
    return;
  }

  $terms = wp_get_post_terms( $post->ID, 'categoria_ayuda', [ 'fields' => 'ids' ] );

  $recurso_id = wp_insert_post( [
    'post_type'    => 'recurso_ayuda',
    'post_status'  => 'publish',
    'post_title'   => $post->post_title,
    'post_content' => $post->post_content,
  ], true );

  if ( is_wp_error( $recurso_id ) ) {
    return;
  }

  if ( $terms && ! is_wp_error( $terms ) ) {
    wp_set_object_terms( $recurso_id, $terms, 'categoria_ayuda' );
  }

  // Carries the submitter's name over so the "Últimas preguntas" sidebar
  // (template-parts/soporte/recent-questions.php) can show an avatar
  // initial - it queries Recurso_Ayuda posts that have this meta key,
  // distinguishing community-submitted answers from editorial FAQ entries.
  $submitter_name = get_field( 'nombre_remitente', $post->ID );

  if ( $submitter_name ) {
    update_post_meta( $recurso_id, '_soporte_submitter_name', $submitter_name );
  }

  update_post_meta( $post->ID, '_recurso_ayuda_id', $recurso_id );
} // end maybe_sync_pregunta_soporte_to_recurso_ayuda
add_action( 'transition_post_status', function( $new_status, $old_status, $post ) {
  maybe_sync_pregunta_soporte_to_recurso_ayuda( $post );
}, 10, 3 );
add_action( 'save_post_pregunta_soporte', __NAMESPACE__ . '\maybe_sync_pregunta_soporte_to_recurso_ayuda' );
