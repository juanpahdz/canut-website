<?php
/**
 * "Ajustes de Scripts" options page (inc/acf-fields/ajustes-scripts.php):
 * three free-text fields (head/body/footer) editable from the admin, echoed
 * verbatim at the matching spot on every page - for Google Tag Manager,
 * tracking pixels, verification meta tags, etc. without a code deploy.
 *
 * Output is intentionally unescaped: this is arbitrary third-party
 * HTML/JS by design (same trust model as any "insert headers and footers"
 * plugin), gated behind the options page's own 'manage_options' capability.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Registers the "Ajustes de Scripts" ACF options page. Deferred to acf/init
 * like the theme's other ACF registration (see inc/hooks.php), and no-ops
 * entirely if ACF Pro (which options pages require) isn't active.
 */
function register_custom_scripts_options_page() {
  if ( ! function_exists( 'acf_add_options_page' ) ) {
    return;
  }

  acf_add_options_page( [
    'page_title' => __( 'Ajustes de Scripts', 'air-light' ),
    'menu_title' => __( 'Scripts', 'air-light' ),
    'menu_slug'  => 'ajustes-scripts',
    'capability' => 'manage_options',
    'icon_url'   => 'dashicons-editor-code',
    'position'   => 82,
  ] );
} // end register_custom_scripts_options_page

/**
 * Raw markup for a scripts_* field, or '' if empty/ACF isn't active.
 *
 * @param string $name ACF field name (scripts_head, scripts_body or scripts_footer).
 * @return string
 */
function get_custom_scripts( $name ) {
  if ( ! function_exists( 'get_field' ) ) {
    return '';
  }

  return trim( (string) get_field( $name, 'option' ) );
} // end get_custom_scripts

function output_custom_head_scripts() {
  $scripts = get_custom_scripts( 'scripts_head' );

  if ( $scripts ) {
    echo $scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw markup from a manage_options-only settings field.
  }
} // end output_custom_head_scripts

function output_custom_body_scripts() {
  $scripts = get_custom_scripts( 'scripts_body' );

  if ( $scripts ) {
    echo $scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw markup from a manage_options-only settings field.
  }
} // end output_custom_body_scripts

function output_custom_footer_scripts() {
  $scripts = get_custom_scripts( 'scripts_footer' );

  if ( $scripts ) {
    echo $scripts . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw markup from a manage_options-only settings field.
  }
} // end output_custom_footer_scripts
