<?php
/**
 * AddContactInfo (custom event - Meta has no standard event for "checkout
 * contact step completed"): fired when the customer confirms the
 * "Información de contacto" step (step 1) and the wizard advances to "Envío"
 * - not on every field blur while they're still typing. Configurable event
 * name, see Ajustes > Facebook Pixel.
 *
 * Hooks Air_Light\checkout_step_continued (inc/hooks/checkout-step-actions.php),
 * the same step-confirmation signal inc/hooks/data-consent.php already
 * listens to - fired once per "Continuar" click on step 1, with $form_data
 * being the whole checkout form parsed from its serialized string
 * (unsanitized, sanitized here same as data-consent.php). Previously hooked
 * woocommerce_checkout_update_order_review, core's AJAX hook that fires on
 * any field edit/blur anywhere in the form, which sent this the moment a
 * valid-looking email+phone existed - often while still on step 1, well
 * before the customer actually confirmed it.
 *
 * Still deduped against the WooCommerce session so re-confirming the same
 * step (e.g. via "Modificar") with an unchanged email+phone doesn't resend.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( __NAMESPACE__ . '\checkout_step_continued', __NAMESPACE__ . '\send_add_contact_info_event', 10, 2 );

/**
 * @param int   $step      Confirmed step number.
 * @param array $form_data Whole checkout form, unsanitized (see checkout_step_continue()).
 */
function send_add_contact_info_event( $step, $form_data ) {
  if ( 1 !== $step ) {
    return;
  }

  if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
    return;
  }

  $email = isset( $form_data['billing_email'] ) ? sanitize_email( $form_data['billing_email'] ) : '';
  $phone = isset( $form_data['billing_phone'] ) ? sanitize_text_field( $form_data['billing_phone'] ) : '';

  if ( ! is_email( $email ) || ! $phone ) {
    return;
  }

  $value_hash = md5( $email . '|' . $phone );

  if ( facebook_capi_session_value_unchanged( 'contact_info', $value_hash ) ) {
    return;
  }

  $sent = send_facebook_event( facebook_capi_custom_event_name( 'facebook_capi_event_name_contact_info', 'AddContactInfo' ), [
    'user_data'   => [
      'em' => facebook_capi_hash( $email ),
      'ph' => facebook_capi_hash( preg_replace( '/\D/', '', $phone ) ),
    ],
    'custom_data' => [
      'content_name' => __( 'Información de contacto', 'air-light' ),
    ],
  ] );

  if ( $sent ) {
    facebook_capi_mark_session_value_sent( 'contact_info', $value_hash );
  }
} // end send_add_contact_info_event
