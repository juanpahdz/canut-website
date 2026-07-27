<?php
/**
 * AddPaymentInfo: fired when the customer confirms the "Método de pago" step
 * (step 3) and the wizard advances to "Método de envío" - narrows the funnel
 * further between "reached checkout" (InitiateCheckout) and "order placed".
 *
 * Hooks Air_Light\checkout_step_continued (inc/hooks/checkout-step-actions.php),
 * same step-confirmation signal add-contact-info.php/add-shipping-info.php
 * use, fired once per "Continuar" click on step 3, with $form_data being the
 * whole checkout form parsed from its serialized string (unsanitized,
 * sanitized here same as those files). Previously hooked
 * woocommerce_checkout_update_order_review, core's own AJAX hook fired by
 * checkout.js right after page load to quote initial shipping/totals - by
 * then a payment gateway is already force-checked to a default by core
 * itself (see WC_Payment_Gateways/checkout.js init_payment_methods()), so
 * that first AJAX call already carried a payment_method and fired this
 * immediately at checkout start, before the customer had chosen (or even
 * seen) a payment method.
 *
 * Still deduped against the WooCommerce session so re-confirming the same
 * step (e.g. via "Modificar") with an unchanged payment method doesn't
 * resend.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( __NAMESPACE__ . '\checkout_step_continued', __NAMESPACE__ . '\send_add_payment_info_event', 10, 2 );

/**
 * @param int   $step      Confirmed step number.
 * @param array $form_data Whole checkout form, unsanitized (see checkout_step_continue()).
 */
function send_add_payment_info_event( $step, $form_data ) {
  if ( 3 !== $step ) {
    return;
  }

  if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
    return;
  }

  $payment_method = isset( $form_data['payment_method'] ) ? sanitize_text_field( $form_data['payment_method'] ) : '';

  if ( ! $payment_method ) {
    return;
  }

  $cart          = WC()->cart;
  $cart_contents = facebook_capi_cart_contents( $cart );

  if ( ! $cart_contents['contents'] ) {
    return;
  }

  $value_hash = md5( $payment_method . '|' . $cart->get_cart_hash() );

  if ( facebook_capi_session_value_unchanged( 'payment_info', $value_hash ) ) {
    return;
  }

  $sent = send_facebook_event( 'AddPaymentInfo', [
    'custom_data' => array_merge( [ 'content_type' => 'product' ], $cart_contents ),
  ] );

  if ( $sent ) {
    facebook_capi_mark_session_value_sent( 'payment_info', $value_hash );
  }
} // end send_add_payment_info_event
