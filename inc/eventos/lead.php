<?php
/**
 * Lead: fired when a visitor submits the Contacto page form (name/email/
 * message) - the site's only form right now (no newsletter signup or
 * wholesale quote form exists yet; wire those to the same
 * Air_Light\lead_submitted action if/when they're added).
 *
 * The form itself is handled by contact_form_submit() in inc/hooks/forms.php,
 * which fires Air_Light\lead_submitted only after wp_mail() actually
 * succeeds - this file only reacts to that action, it doesn't touch the form
 * handling itself.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( __NAMESPACE__ . '\lead_submitted', __NAMESPACE__ . '\send_lead_event' );

/**
 * @param array $data { @type string $email @type string $phone @type string $name }
 */
function send_lead_event( $data ) {
  $email = $data['email'] ?? '';

  if ( ! $email ) {
    return;
  }

  send_facebook_event( 'Lead', [
    // 1-minute bucket, in case of an accidental double form submit (double
    // click on the submit button) - not meant to block a genuinely new lead
    // from the same person minutes later.
    'dedup_key'   => $email . '|' . gmdate( 'YmdHi' ),
    'dedup_ttl'   => 5 * MINUTE_IN_SECONDS,
    'user_data'   => [
      'em' => facebook_capi_hash( $email ),
      'ph' => ! empty( $data['phone'] ) ? facebook_capi_hash( preg_replace( '/\D/', '', $data['phone'] ) ) : '',
      'fn' => ! empty( $data['name'] ) ? facebook_capi_hash( $data['name'] ) : '',
    ],
    'custom_data' => [
      'content_name' => __( 'Formulario de contacto', 'air-light' ),
    ],
  ] );
} // end send_lead_event
