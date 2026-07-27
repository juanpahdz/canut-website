<?php
/**
 * Core sender for Meta's Conversions API (server-side Facebook Pixel
 * events). Every event file in this folder (view-content.php, add-to-cart.php,
 * initiate-checkout.php, add-payment-info.php, purchase.php, lead.php,
 * complete-registration.php) calls send_facebook_event() below instead of
 * talking to the Graph API directly.
 *
 * Deliberately backend-only: no browser-side fbq() pixel calls anywhere in
 * the theme, so there is only ever one source sending each event to Meta -
 * the duplicate-event problem a client pixel + a server pixel both firing the
 * same action causes (inflated conversion counts in Ads Manager) doesn't
 * apply here. What can still happen is the *same* PHP hook firing twice for
 * one real action (a double AJAX call, a hook attached more than once) - the
 * dedup_key/facebook_event_already_sent() guard below exists for that, not
 * for client/server deduplication.
 *
 * Credentials are set in Ajustes > Facebook Pixel (see
 * inc/acf-fields/ajustes-facebook-pixel.php).
 *
 * @see https://developers.facebook.com/docs/marketing-api/conversions-api
 * @package air-light
 */

namespace Air_Light;

/**
 * Registers the "Ajustes de Facebook Pixel" ACF options page. Deferred to
 * acf/init like the theme's other ACF registration (see inc/hooks.php), and
 * no-ops entirely if ACF Pro (which options pages require) isn't active.
 */
function register_facebook_pixel_options_page() {
  if ( ! function_exists( 'acf_add_options_page' ) ) {
    return;
  }

  acf_add_options_page( [
    'page_title' => __( 'Ajustes de Facebook Pixel', 'air-light' ),
    'menu_title' => __( 'Facebook Pixel', 'air-light' ),
    'menu_slug'  => 'ajustes-facebook-pixel',
    'capability' => 'manage_options',
    'icon_url'   => 'dashicons-facebook-alt',
    'position'   => 81,
  ] );
} // end register_facebook_pixel_options_page

/**
 * Facebook Pixel / Conversions API settings from Ajustes > Facebook Pixel,
 * cached for the rest of the request.
 *
 * @return array{enabled: bool, pixel_id: string, access_token: string, test_event_code: string}
 */
function facebook_pixel_settings() {
  static $settings = null;

  if ( null !== $settings ) {
    return $settings;
  }

  if ( ! function_exists( 'get_field' ) ) {
    $settings = [
      'enabled'         => false,
      'pixel_id'        => '',
      'access_token'    => '',
      'test_event_code' => '',
    ];

    return $settings;
  }

  $settings = [
    'enabled'         => (bool) get_field( 'facebook_pixel_enabled', 'option' ),
    'pixel_id'        => trim( (string) get_field( 'facebook_pixel_id', 'option' ) ),
    'access_token'    => trim( (string) get_field( 'facebook_capi_access_token', 'option' ) ),
    'test_event_code' => trim( (string) get_field( 'facebook_capi_test_event_code', 'option' ) ),
  ];

  return $settings;
} // end facebook_pixel_settings

/**
 * Configurable name for a custom event (one with no Meta standard
 * equivalent - AddContactInfo, AddShippingInfo, InitiatePayment), read from
 * its own text field on Ajustes > Facebook Pixel (see
 * inc/acf-fields/ajustes-facebook-pixel.php's "Eventos personalizados" tab)
 * so it can be renamed to match whatever a Custom Conversion is set up to
 * look for in Ads Manager, without a code deploy.
 *
 * @param string $field_name ACF field name for this custom event's name.
 * @param string $default    Fallback event name if the field is left empty or ACF isn't active.
 * @return string
 */
function facebook_capi_custom_event_name( $field_name, $default ) {
  if ( ! function_exists( 'get_field' ) ) {
    return $default;
  }

  $value = trim( (string) get_field( $field_name, 'option' ) );

  return $value ?: $default;
} // end facebook_capi_custom_event_name

/**
 * Whether events can actually be sent: toggle on, plus both a Pixel ID and an
 * access token configured.
 *
 * @return bool
 */
function facebook_pixel_is_configured() {
  $settings = facebook_pixel_settings();

  return $settings['enabled'] && $settings['pixel_id'] && $settings['access_token'];
} // end facebook_pixel_is_configured

/**
 * Normalizes and SHA-256 hashes a piece of personal data (email, phone, etc.)
 * the way Meta's user_data fields (em, ph, fn, ln, ...) require.
 *
 * @param string $value Raw value.
 * @return string Hashed value, or '' if $value was empty.
 */
function facebook_capi_hash( $value ) {
  $value = strtolower( trim( (string) $value ) );

  return $value ? hash( 'sha256', $value ) : '';
} // end facebook_capi_hash

/**
 * Shared user_data block sent with every event: IP/user agent (always
 * available server-side), fbp/fbc click id cookies if present, and whatever
 * billing details WooCommerce already knows about the current visitor
 * (WC()->customer, populated as soon as they type into the checkout form -
 * works for guests too, not just logged-in accounts) or the logged-in
 * WordPress user. Per-event data (em/ph passed explicitly from a form
 * submission, external_id, etc.) is merged in by the caller via $extra.
 *
 * @param array $extra Event-specific user_data fields, merged in last (wins on conflict).
 * @return array
 */
function facebook_capi_user_data( $extra = [] ) {
  $user_data = [];

  $ip = get_client_ip_address();
  if ( $ip ) {
    $user_data['client_ip_address'] = $ip;
  }

  if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    $user_data['client_user_agent'] = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
  }

  if ( ! empty( $_COOKIE['_fbp'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $user_data['fbp'] = sanitize_text_field( wp_unslash( $_COOKIE['_fbp'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  }

  if ( ! empty( $_COOKIE['_fbc'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $user_data['fbc'] = sanitize_text_field( wp_unslash( $_COOKIE['_fbc'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
  }

  if ( function_exists( 'WC' ) && WC()->customer ) {
    $customer = WC()->customer;

    $mapped_fields = [
      'em'      => $customer->get_billing_email(),
      'ph'      => $customer->get_billing_phone() ? preg_replace( '/\D/', '', $customer->get_billing_phone() ) : '',
      'fn'      => $customer->get_billing_first_name(),
      'ln'      => $customer->get_billing_last_name(),
      'ct'      => $customer->get_billing_city(),
      'st'      => $customer->get_billing_state(),
      'zp'      => $customer->get_billing_postcode(),
      'country' => $customer->get_billing_country(),
    ];

    foreach ( $mapped_fields as $field => $raw_value ) {
      if ( '' !== $raw_value ) {
        $user_data[ $field ] = facebook_capi_hash( $raw_value );
      }
    }
  }

  if ( is_user_logged_in() ) {
    $user_data['external_id'] = facebook_capi_hash( get_current_user_id() );

    $current_user = wp_get_current_user();
    if ( empty( $user_data['em'] ) && $current_user->user_email ) {
      $user_data['em'] = facebook_capi_hash( $current_user->user_email );
    }
  }

  return array_merge( $user_data, array_filter( $extra ) );
} // end facebook_capi_user_data

/**
 * Dedup guard so the same real-world action (a click, a page load) never
 * reaches the Graph API twice - e.g. a hook that (due to a plugin conflict or
 * a double AJAX call) fires more than once for what is, from the visitor's
 * side, a single add-to-cart or page view. Each event file picks its own
 * $identifier (product/cart-item/session based) and $ttl matching how long a
 * repeat within that window should be considered "the same" occurrence.
 *
 * @param string $event_name Facebook event name, e.g. 'ViewContent'.
 * @param string $identifier Anything that uniquely identifies this occurrence.
 * @return bool
 */
function facebook_event_already_sent( $event_name, $identifier ) {
  return (bool) get_transient( 'fb_capi_' . md5( $event_name . '|' . $identifier ) );
} // end facebook_event_already_sent

/**
 * Marks an occurrence as sent, see facebook_event_already_sent() above.
 *
 * @param string $event_name Facebook event name.
 * @param string $identifier Same identifier passed to facebook_event_already_sent().
 * @param int    $ttl        Seconds before the same occurrence can send again.
 */
function facebook_mark_event_sent( $event_name, $identifier, $ttl = HOUR_IN_SECONDS ) {
  set_transient( 'fb_capi_' . md5( $event_name . '|' . $identifier ), 1, $ttl );
} // end facebook_mark_event_sent

/**
 * Session-scoped dedup for checkout-progress events (AddContactInfo/
 * AddShippingInfo/AddPaymentInfo - see their own files under inc/eventos/):
 * true if $value_hash is exactly the same value last sent for $event_key in
 * this WooCommerce session. Storing in WC()->session instead of a transient
 * with a fixed TTL ties the guard to the checkout session's own real
 * lifetime - a customer sitting on checkout for over half an hour without
 * touching a field can't cause a spurious resend just because a fixed window
 * elapsed, and it still refires the moment the value genuinely changes (or
 * changes back - overwriting, not accumulating, past values).
 *
 * @param string $event_key  Fixed internal identifier for the event (e.g. 'contact_info') - NOT the customizable Facebook event name, which could be renamed/emptied from Ajustes > Facebook Pixel.
 * @param string $value_hash Hash representing the current field value(s).
 * @return bool
 */
function facebook_capi_session_value_unchanged( $event_key, $value_hash ) {
  if ( ! function_exists( 'WC' ) || ! WC()->session ) {
    return false;
  }

  return WC()->session->get( 'fb_capi_' . $event_key ) === $value_hash;
} // end facebook_capi_session_value_unchanged

/**
 * Marks $value_hash as the last value sent for $event_key in this session,
 * see facebook_capi_session_value_unchanged() above.
 *
 * @param string $event_key  Same fixed identifier passed to facebook_capi_session_value_unchanged().
 * @param string $value_hash Hash representing the value just sent.
 */
function facebook_capi_mark_session_value_sent( $event_key, $value_hash ) {
  if ( function_exists( 'WC' ) && WC()->session ) {
    WC()->session->set( 'fb_capi_' . $event_key, $value_hash );
  }
} // end facebook_capi_mark_session_value_sent

/**
 * Cart contents formatted as Meta's custom_data.contents/content_ids/value -
 * shared by InitiateCheckout and AddPaymentInfo, which both describe the same
 * "cart at this point in checkout" snapshot.
 *
 * @param \WC_Cart $cart Cart to describe.
 * @return array{contents: array, content_ids: array, num_items: int, value: float, currency: string}
 */
function facebook_capi_cart_contents( $cart ) {
  $contents = [];

  foreach ( $cart->get_cart() as $cart_item ) {
    $product = $cart_item['data'];

    if ( ! $product instanceof \WC_Product ) {
      continue;
    }

    $contents[] = [
      'id'         => $product->get_sku() ?: (string) $product->get_id(),
      'quantity'   => $cart_item['quantity'],
      'item_price' => (float) wc_get_price_to_display( $product ),
    ];
  }

  return [
    'contents'    => $contents,
    'content_ids' => wp_list_pluck( $contents, 'id' ),
    'num_items'   => $cart->get_cart_contents_count(),
    'value'       => (float) $cart->get_total( 'edit' ),
    'currency'    => get_woocommerce_currency(),
  ];
} // end facebook_capi_cart_contents

/**
 * Sends one event to Meta's Conversions API.
 *
 * @param string $event_name Facebook standard event name (ViewContent, AddToCart, ...).
 * @param array  $args {
 *   @type string $dedup_key   Optional. Identifier passed to facebook_event_already_sent()/facebook_mark_event_sent(); skipped entirely if omitted.
 *   @type int    $dedup_ttl   Optional. Seconds the dedup guard lasts, default HOUR_IN_SECONDS.
 *   @type string $event_id    Optional. Explicit event id, otherwise a UUID is generated.
 *   @type array  $user_data   Optional. Extra user_data fields (em/ph/external_id/...), merged over the shared defaults.
 *   @type array  $custom_data Optional. Event-specific fields: currency, value, content_ids, content_type, contents, content_name, num_items.
 * }
 * @return bool Whether the event was actually sent (false if unconfigured, deduped, or the request failed).
 */
function send_facebook_event( $event_name, $args = [] ) {
  if ( ! facebook_pixel_is_configured() ) {
    facebook_capi_debug_log( [
      'event'  => $event_name,
      'sent'   => false,
      'reason' => 'not_configured',
    ] );

    return false;
  }

  $dedup_key = $args['dedup_key'] ?? null;

  if ( $dedup_key && facebook_event_already_sent( $event_name, $dedup_key ) ) {
    facebook_capi_debug_log( [
      'event'  => $event_name,
      'sent'   => false,
      'reason' => 'deduped',
    ] );

    return false;
  }

  $settings = facebook_pixel_settings();

  $event_data = [
    'event_name'       => $event_name,
    'event_time'       => time(),
    'event_id'         => $args['event_id'] ?? wp_generate_uuid4(),
    'event_source_url' => home_url( add_query_arg( null, null ) ),
    'action_source'    => 'website',
    'user_data'        => facebook_capi_user_data( $args['user_data'] ?? [] ),
    'custom_data'      => $args['custom_data'] ?? [],
  ];

  $body = [
    'access_token' => $settings['access_token'],
    'data'         => wp_json_encode( [ $event_data ] ),
  ];

  if ( $settings['test_event_code'] ) {
    $body['test_event_code'] = $settings['test_event_code'];
  }

  $response = wp_remote_post( sprintf( 'https://graph.facebook.com/v21.0/%s/events', $settings['pixel_id'] ), [
    'timeout' => 8,
    'body'    => $body,
  ] );

  if ( is_wp_error( $response ) ) {
    error_log( sprintf( '[Facebook Conversions API] %s: %s', $event_name, $response->get_error_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- deliberate: surfaces delivery failures for a fire-and-forget background request with no other visible feedback.

    facebook_capi_debug_log( [
      'event'  => $event_name,
      'sent'   => false,
      'reason' => 'request_failed: ' . $response->get_error_message(),
    ] );

    return false;
  }

  $status = wp_remote_retrieve_response_code( $response );

  if ( $status < 200 || $status >= 300 ) {
    error_log( sprintf( '[Facebook Conversions API] %s: HTTP %d - %s', $event_name, $status, wp_remote_retrieve_body( $response ) ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- see above.

    facebook_capi_debug_log( [
      'event'  => $event_name,
      'sent'   => false,
      'reason' => 'http_' . $status,
    ] );

    return false;
  }

  if ( $dedup_key ) {
    facebook_mark_event_sent( $event_name, $dedup_key, $args['dedup_ttl'] ?? HOUR_IN_SECONDS );
  }

  facebook_capi_debug_log( [
    'event' => $event_name,
    'sent'  => true,
  ] );

  return true;
} // end send_facebook_event

/**
 * TEMPORARY debug aid for confirming InitiateCheckout/AddContactInfo/
 * AddShippingInfo/AddPaymentInfo actually fire at the right moments while
 * testing the checkout wizard - see inc/eventos/initiate-checkout.php (echoes
 * this to the console on page load) and inc/hooks/checkout-step-actions.php
 * (returns this in the step-confirm AJAX response, logged client-side by
 * postStepContinued() in modules/checkout-steps-canut.js). Only ever recorded/
 * surfaced when WP_DEBUG is on, never in production. Safe to remove, along
 * with its two call sites above, once the four events are confirmed firing
 * correctly.
 *
 * @param array|null $entry Entry to record (['event' => string, 'sent' => bool, 'reason' => string]), or null to just read the log so far.
 * @return array Entries recorded so far this request.
 */
function facebook_capi_debug_log( $entry = null ) {
  static $log = [];

  if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
    return $log;
  }

  if ( null !== $entry ) {
    $log[] = $entry;
  }

  return $log;
} // end facebook_capi_debug_log
