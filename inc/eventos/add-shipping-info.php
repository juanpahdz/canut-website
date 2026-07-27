<?php
/**
 * AddShippingInfo (custom event - Meta has no standard event for "checkout
 * shipping step completed"): fired when the customer confirms the "Envío"
 * step (step 2) and the wizard advances to "Método de pago" - not on every
 * address field blur while they're still typing. Configurable event name,
 * see Ajustes > Facebook Pixel.
 *
 * Hooks Air_Light\checkout_step_continued (inc/hooks/checkout-step-actions.php),
 * same step-confirmation signal add-contact-info.php/inc/hooks/data-consent.php
 * use, fired once per "Continuar" click on step 2, with $form_data being the
 * whole checkout form parsed from its serialized string (unsanitized,
 * sanitized here same as data-consent.php). Previously hooked
 * woocommerce_checkout_update_order_review, core's AJAX hook that fires on
 * any field edit/blur anywhere in the form - which meant this could fire (or
 * not) independent of whether the customer had actually finished/confirmed
 * the step.
 *
 * Still deduped against the WooCommerce session so re-confirming the same
 * step (e.g. via "Modificar") with an unchanged address doesn't resend.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( __NAMESPACE__ . '\checkout_step_continued', __NAMESPACE__ . '\send_add_shipping_info_event', 10, 2 );

/**
 * @param int   $step      Confirmed step number.
 * @param array $form_data Whole checkout form, unsanitized (see checkout_step_continue()).
 */
function send_add_shipping_info_event( $step, $form_data ) {
  if ( 2 !== $step ) {
    return;
  }

  if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
    return;
  }

  $address  = isset( $form_data['billing_address_1'] ) ? sanitize_text_field( $form_data['billing_address_1'] ) : '';
  $city     = isset( $form_data['billing_city'] ) ? sanitize_text_field( $form_data['billing_city'] ) : '';
  $postcode = isset( $form_data['billing_postcode'] ) ? sanitize_text_field( $form_data['billing_postcode'] ) : '';

  if ( ! $address || ! $city || ! $postcode ) {
    return;
  }

  $user_data = [
    'ct' => facebook_capi_hash( $city ),
    'zp' => facebook_capi_hash( $postcode ),
  ];

  if ( ! empty( $form_data['billing_state'] ) ) {
    $user_data['st'] = facebook_capi_hash( sanitize_text_field( $form_data['billing_state'] ) );
  }

  if ( ! empty( $form_data['billing_country'] ) ) {
    $user_data['country'] = facebook_capi_hash( sanitize_text_field( $form_data['billing_country'] ) );
  }

  $value_hash = md5( $address . '|' . $city . '|' . $postcode );

  if ( facebook_capi_session_value_unchanged( 'shipping_info', $value_hash ) ) {
    return;
  }

  $sent = send_facebook_event( facebook_capi_custom_event_name( 'facebook_capi_event_name_shipping_info', 'AddShippingInfo' ), [
    'user_data'   => $user_data,
    'custom_data' => [
      'content_name' => __( 'Información de envío', 'air-light' ),
    ],
  ] );

  if ( $sent ) {
    facebook_capi_mark_session_value_sent( 'shipping_info', $value_hash );
  }
} // end send_add_shipping_info_event
