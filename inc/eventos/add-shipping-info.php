<?php
/**
 * AddShippingInfo (custom event - Meta has no standard event for "checkout
 * shipping step completed"): fired once the checkout form has an address,
 * city and postcode, i.e. the "Envío" step's fields are actually filled in.
 * Configurable event name, see Ajustes > Facebook Pixel.
 *
 * Same mechanism as add-payment-info.php/add-contact-info.php:
 * woocommerce_checkout_update_order_review fires on every field edit/blur,
 * so it's gated on the actual field values and deduped against the
 * WooCommerce session so it only sends once per distinct address for the
 * whole checkout session - not on every unrelated field the same AJAX cycle
 * also runs for, and not a spurious resend just because time passed with
 * the address unchanged.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_checkout_update_order_review', __NAMESPACE__ . '\send_add_shipping_info_event' );

/**
 * @param string $post_data Serialized checkout form fields (query-string encoded).
 */
function send_add_shipping_info_event( $post_data ) {
  if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
    return;
  }

  parse_str( (string) $post_data, $data );

  $address  = isset( $data['billing_address_1'] ) ? sanitize_text_field( $data['billing_address_1'] ) : '';
  $city     = isset( $data['billing_city'] ) ? sanitize_text_field( $data['billing_city'] ) : '';
  $postcode = isset( $data['billing_postcode'] ) ? sanitize_text_field( $data['billing_postcode'] ) : '';

  if ( ! $address || ! $city || ! $postcode ) {
    return;
  }

  $user_data = [
    'ct' => facebook_capi_hash( $city ),
    'zp' => facebook_capi_hash( $postcode ),
  ];

  if ( ! empty( $data['billing_state'] ) ) {
    $user_data['st'] = facebook_capi_hash( sanitize_text_field( $data['billing_state'] ) );
  }

  if ( ! empty( $data['billing_country'] ) ) {
    $user_data['country'] = facebook_capi_hash( sanitize_text_field( $data['billing_country'] ) );
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
