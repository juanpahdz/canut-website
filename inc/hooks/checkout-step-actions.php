<?php
/**
 * Generic checkout-wizard step-confirmation hook. modules/checkout-steps-canut.js
 * posts here every time a customer confirms a step via its own "Continuar"
 * button (Información de contacto / Envío / Método de pago - the wizard's
 * steps that otherwise never make a server round trip at all, see that
 * file's own docblock). Fires a plain WordPress action other code can hook
 * into, so a new "run this when step N is confirmed" doesn't need touching
 * this file or the JS again - see inc/hooks/data-consent.php for the first
 * listener (logs data-processing consent the moment step 1 is confirmed).
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Loads the ajax_url/nonce checkout-steps-canut.js needs, only on the actual
 * checkout page - not the thank-you page (is_checkout() covers both, same
 * exclusion checkout_summary_localize_script() already needs).
 */
function checkout_step_actions_localize_script() {
  if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ) {
    return;
  }

  wp_localize_script( 'scripts', 'air_light_checkoutStepActions', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'canut_checkout_step_continue' ),
  ] );
} // end checkout_step_actions_localize_script
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\checkout_step_actions_localize_script', 20 );

/**
 * AJAX: fires Air_Light\checkout_step_continued for whichever listener wants
 * it. $_POST['form_data'] is the whole checkout form serialized (same
 * "parse the whole form, use what you need" approach
 * add-contact-info.php/add-shipping-info.php already use for the
 * update_order_review AJAX cycle) rather than trying to keep this endpoint's
 * payload in sync with exactly which fields belong to which step.
 *
 * Fire-and-forget from the JS side (see checkout-steps-canut.js) - a slow or
 * failed request here must never block the wizard from advancing to the next
 * step, so this has no bearing on the actual checkout flow either way.
 */
function checkout_step_continue() {
  check_ajax_referer( 'canut_checkout_step_continue', 'nonce' );

  $step = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 0;

  if ( ! $step ) {
    wp_send_json_error();
  }

  parse_str( isset( $_POST['form_data'] ) ? wp_unslash( $_POST['form_data'] ) : '', $form_data ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- parsed here, individual fields sanitized by each Air_Light\checkout_step_continued listener as it reads them.

  $accepted_at = isset( $_POST['accepted_at'] ) ? sanitize_text_field( wp_unslash( $_POST['accepted_at'] ) ) : '';

  /**
   * @param int    $step        Confirmed step number (1 = Información de contacto, 2 = Envío, 3 = Método de pago).
   * @param array  $form_data   Whole checkout form, parsed from its serialized string - unsanitized, sanitize whatever field you read.
   * @param string $accepted_at Client-reported ISO timestamp of the moment "Continuar" was clicked - never trust blindly, validate before use.
   */
  do_action( __NAMESPACE__ . '\checkout_step_continued', $step, $form_data, $accepted_at );

  wp_send_json_success();
} // end checkout_step_continue
add_action( 'wp_ajax_canut_checkout_step_continue', __NAMESPACE__ . '\checkout_step_continue' );
add_action( 'wp_ajax_nopriv_canut_checkout_step_continue', __NAMESPACE__ . '\checkout_step_continue' );
