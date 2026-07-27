<?php
/**
 * CompleteRegistration: fired when a real customer account is created - not
 * a guest checkout. woocommerce_created_customer covers both ways that can
 * happen on this site: ticking "crear una cuenta" at checkout, or the
 * standalone My Account registration form.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'woocommerce_created_customer', __NAMESPACE__ . '\send_complete_registration_event' );

/**
 * @param int $customer_id Newly created user id.
 */
function send_complete_registration_event( $customer_id ) {
  // Permanent guard, not a transient: an account is only ever "just created"
  // once, so this event should never be able to refire for the same user.
  if ( get_user_meta( $customer_id, '_facebook_capi_registration_sent', true ) ) {
    return;
  }

  $user = get_userdata( $customer_id );

  if ( ! $user ) {
    return;
  }

  $sent = send_facebook_event( 'CompleteRegistration', [
    'user_data'   => [
      'em'          => facebook_capi_hash( $user->user_email ),
      'external_id' => facebook_capi_hash( $customer_id ),
    ],
    'custom_data' => [
      'content_name' => __( 'Registro de cliente', 'air-light' ),
    ],
  ] );

  if ( $sent ) {
    update_user_meta( $customer_id, '_facebook_capi_registration_sent', 1 );
  }
} // end send_complete_registration_event
