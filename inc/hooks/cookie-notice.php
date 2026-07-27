<?php
/**
 * Site-wide cookie notice - rendered on every page (footer.php), including
 * checkout, unlike the WhatsApp float which is intentionally hidden there.
 * Text/button label editable from Ajustes > Cookies
 * (inc/acf-fields/ajustes-cookies.php) without a code deploy.
 *
 * Visibility itself is decided client-side (modules/cookie-notice-canut.js
 * checks document.cookie for the "already accepted" cookie) rather than here
 * server-side - keeps this correct under page caching, where a server-side
 * check could otherwise serve a visitor who already accepted the same cached
 * "still hidden" state, or vice versa.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Registers the "Ajustes de Cookies" ACF options page. Deferred to acf/init
 * like the theme's other ACF registration (see inc/hooks.php), and no-ops
 * entirely if ACF Pro (which options pages require) isn't active.
 */
function register_cookie_notice_options_page() {
  if ( ! function_exists( 'acf_add_options_page' ) ) {
    return;
  }

  acf_add_options_page( [
    'page_title' => __( 'Ajustes de Cookies', 'air-light' ),
    'menu_title' => __( 'Cookies', 'air-light' ),
    'menu_slug'  => 'ajustes-cookies',
    'capability' => 'manage_options',
    'icon_url'   => 'dashicons-shield',
    'position'   => 84,
  ] );
} // end register_cookie_notice_options_page

/**
 * @return bool
 */
function is_cookie_notice_enabled() {
  return function_exists( 'get_field' ) && (bool) get_field( 'cookie_notice_enabled', 'option' );
} // end is_cookie_notice_enabled

/**
 * @return string
 */
function get_cookie_notice_text() {
  return function_exists( 'get_field' ) ? trim( (string) get_field( 'cookie_notice_text', 'option' ) ) : '';
} // end get_cookie_notice_text

/**
 * @return string
 */
function get_cookie_notice_button_label() {
  $label = function_exists( 'get_field' ) ? trim( (string) get_field( 'cookie_notice_button_label', 'option' ) ) : '';

  return $label ?: __( 'Aceptar', 'air-light' );
} // end get_cookie_notice_button_label

/**
 * Renders the cookie notice bar, called from footer.php. Starts with the
 * `hidden` attribute in the markup itself - modules/cookie-notice-canut.js
 * removes it only if the visitor hasn't already accepted (see this file's
 * own docblock for why that check happens client-side, not here).
 */
function render_cookie_notice() {
  if ( ! is_cookie_notice_enabled() ) {
    return;
  }

  $text = get_cookie_notice_text();

  if ( ! $text ) {
    return;
  }

  $policy_page = get_page_by_path( 'politica-de-cookies' );
  ?>
  <div class="cookie-notice-canut" data-cookie-notice hidden>
    <div class="cookie-notice-canut-inner">
      <p class="cookie-notice-canut-text">
        <?php echo esc_html( $text ); ?>
        <?php if ( $policy_page ) : ?>
          <a href="<?php echo esc_url( get_permalink( $policy_page ) ); ?>" class="cookie-notice-canut-link">
            <?php esc_html_e( 'Política de cookies', 'air-light' ); ?>
          </a>
        <?php endif; ?>
      </p>
      <button type="button" class="button-canut-base button-canut-primary cookie-notice-canut-button" data-cookie-notice-accept>
        <?php echo esc_html( get_cookie_notice_button_label() ); ?>
      </button>
    </div>
  </div>
  <?php
} // end render_cookie_notice
