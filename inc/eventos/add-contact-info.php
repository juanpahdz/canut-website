<?php
/**
 * AddContactInfo (custom event - Meta has no standard event for "checkout
 * contact step completed"): fired once the checkout form has a valid email
 * and a phone number, i.e. the "Información de contacto" step's fields are
 * actually filled in. Configurable event name, see Ajustes > Facebook Pixel.
 *
 * Same mechanism as add-payment-info.php: woocommerce_checkout_update_order_review
 * is core's own AJAX hook, fired on every field edit/blur during checkout,
 * so it's gated on the actual field values (not just "the hook fired") and
 * deduped against the WooCommerce session so it only sends once per distinct
 * email+phone combo for the whole checkout session - not on every unrelated
 * field the same AJAX cycle also runs for (address, payment method, ...),
 * and not a spurious resend just because the customer took a while filling
 * in the rest of the form.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_checkout_update_order_review', __NAMESPACE__ . '\send_add_contact_info_event' );

/**
 * @param string $post_data Serialized checkout form fields (query-string encoded).
 */
function send_add_contact_info_event( $post_data ) {
  if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
    return;
  }

  parse_str( (string) $post_data, $data );

  $email = isset( $data['billing_email'] ) ? sanitize_email( $data['billing_email'] ) : '';
  $phone = isset( $data['billing_phone'] ) ? sanitize_text_field( $data['billing_phone'] ) : '';

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
