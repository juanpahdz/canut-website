<?php
/**
 * Data-processing consent audit log (Colombia's Ley 1581 de 2012, Habeas
 * Data). checkout_render_data_consent_field()/checkout_validate_data_consent()/
 * checkout_save_data_consent() (inc/hooks/checkout.php) already require and
 * record the checkbox - but only once the whole checkout form is actually
 * submitted, as a single timestamp on the order itself. If a customer
 * accepts it in step 1 ("Información de contacto") and abandons before ever
 * placing the order, that acceptance previously left no record anywhere.
 *
 * This listens for step 1 being confirmed (Air_Light\checkout_step_continued,
 * inc/hooks/checkout-step-actions.php) and logs the exact moment into its own
 * database table - independent of whether an order ever completes - then
 * links the row to the resulting order once/if one is created. Viewable from
 * wp-admin under its own "Consentimientos" menu (inc/admin/consent-log.php).
 *
 * @package air-light
 */

namespace Air_Light;

const DATA_CONSENT_DB_VERSION = '1.0.0';

/**
 * @return string wpdb-prefixed consent log table name.
 */
function data_consent_table() {
  global $wpdb;

  return $wpdb->prefix . 'canut_data_consent';
} // end data_consent_table

/**
 * Creates/updates the consent log table via dbDelta(), version-gated so it
 * only actually runs on a real schema change. Checked on every 'init' rather
 * than a theme (de)activation hook, so a plain code deploy to an
 * already-active theme still gets the table without needing a re-activation.
 */
function maybe_create_data_consent_table() {
  if ( get_option( 'canut_data_consent_db_version' ) === DATA_CONSENT_DB_VERSION ) {
    return;
  }

  global $wpdb;
  require_once ABSPATH . 'wp-admin/includes/upgrade.php';

  $table           = data_consent_table();
  $charset_collate = $wpdb->get_charset_collate();

  // dbDelta() is picky about formatting: two spaces before PRIMARY KEY, each
  // column/key on its own line, no trailing comma on the last one.
  dbDelta( "CREATE TABLE {$table} (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    order_id BIGINT UNSIGNED DEFAULT NULL,
    session_id VARCHAR(64) DEFAULT NULL,
    ip_address VARCHAR(100) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    accepted_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY  (id),
    KEY email (email),
    KEY order_id (order_id),
    KEY session_id (session_id)
  ) {$charset_collate};" );

  update_option( 'canut_data_consent_db_version', DATA_CONSENT_DB_VERSION );
} // end maybe_create_data_consent_table
add_action( 'init', __NAMESPACE__ . '\maybe_create_data_consent_table' );

/**
 * Records data-processing consent the moment step 1 is confirmed. No-ops for
 * any other step, or if the checkbox/email/phone aren't actually present -
 * the wizard's own client-side validation already requires all three before
 * this fires (checkout_render_data_consent_field(), inc/hooks/checkout.php),
 * but that's never trusted alone.
 *
 * @param int    $step        Confirmed step number.
 * @param array  $form_data   Whole checkout form, unsanitized (see checkout_step_continue()).
 * @param string $accepted_at Client-reported ISO timestamp of the moment "Continuar" was clicked.
 */
function record_data_consent( $step, $form_data, $accepted_at ) {
  if ( 1 !== $step || empty( $form_data['data_processing_consent'] ) ) {
    return;
  }

  $email = isset( $form_data['billing_email'] ) ? sanitize_email( $form_data['billing_email'] ) : '';
  $phone = isset( $form_data['billing_phone'] ) ? sanitize_text_field( $form_data['billing_phone'] ) : '';

  if ( ! is_email( $email ) || ! $phone ) {
    return;
  }

  // Only trust the client-reported click moment if it actually parses to a
  // real date - falls back to "now" rather than reject the whole record over
  // a malformed/missing value (a customer's clock being off is still a real
  // acceptance, just logged with the server's own moment instead).
  $accepted_timestamp = $accepted_at ? strtotime( $accepted_at ) : false;
  $accepted_mysql     = $accepted_timestamp
    ? gmdate( 'Y-m-d H:i:s', $accepted_timestamp )
    : current_time( 'mysql', true );

  global $wpdb;

  $wpdb->insert(
    data_consent_table(),
    [
      'email'       => $email,
      'phone'       => $phone,
      'user_id'     => get_current_user_id() ?: null,
      'session_id'  => ( function_exists( 'WC' ) && WC()->session ) ? (string) WC()->session->get_customer_id() : null,
      'ip_address'  => get_client_ip_address(),
      'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
      'accepted_at' => $accepted_mysql,
      'created_at'  => current_time( 'mysql', true ),
    ],
    [ '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' ]
  );
} // end record_data_consent
add_action( __NAMESPACE__ . '\checkout_step_continued', __NAMESPACE__ . '\record_data_consent', 10, 3 );

/**
 * Links a consent log row to the order it actually led to, once/if that
 * order is created. Tries the WooCommerce session id first (most precise -
 * matches the exact browser session that gave consent), then falls back to
 * the order's own billing email against the most recent still-unlinked row
 * from the last 24 hours - the session id can occasionally not match a
 * genuinely-the-same checkout (a session cookie re-issued mid-checkout,
 * etc.), and this is a real audit log, so a best-effort match is better than
 * silently leaving "Pedido" blank when the order is right there.
 *
 * Either way this takes the latest matching row, not every match - a
 * customer can reopen/re-confirm step 1 more than once per session.
 *
 * @param int $order_id Order just created.
 */
function link_data_consent_to_order( $order_id ) {
  $order = wc_get_order( $order_id );

  if ( ! $order instanceof \WC_Order ) {
    return;
  }

  global $wpdb;
  $table = data_consent_table();

  $session_id = ( function_exists( 'WC' ) && WC()->session ) ? (string) WC()->session->get_customer_id() : '';
  $linked     = false;

  if ( $session_id ) {
    $linked = (bool) $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table, no dedicated API.
      $wpdb->prepare(
        "UPDATE {$table}
         SET order_id = %d
         WHERE session_id = %s AND order_id IS NULL
         ORDER BY id DESC
         LIMIT 1",
        $order_id,
        $session_id
      )
    );
  }

  if ( $linked ) {
    return;
  }

  $email = $order->get_billing_email();

  if ( ! $email ) {
    return;
  }

  $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    $wpdb->prepare(
      "UPDATE {$table}
       SET order_id = %d
       WHERE email = %s AND order_id IS NULL AND accepted_at >= %s
       ORDER BY id DESC
       LIMIT 1",
      $order_id,
      $email,
      gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS )
    )
  );
} // end link_data_consent_to_order
add_action( 'woocommerce_checkout_order_processed', __NAMESPACE__ . '\link_data_consent_to_order' );
