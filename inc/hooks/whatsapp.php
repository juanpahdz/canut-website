<?php
/**
 * Single source of truth for the site's WhatsApp numbers (Ventas/Soporte),
 * editable from Ajustes > WhatsApp (see inc/acf-fields/ajustes-whatsapp.php).
 * Every template that links out to WhatsApp calls get_whatsapp_url()/
 * get_whatsapp_number() below instead of hardcoding a number or wa.me link.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Registers the "WhatsApp" ACF options page. Deferred to acf/init like the
 * theme's other ACF registration (see inc/hooks.php), and no-ops entirely if
 * ACF Pro (which options pages require) isn't active.
 */
function register_whatsapp_options_page() {
  if ( ! function_exists( 'acf_add_options_page' ) ) {
    return;
  }

  acf_add_options_page( [
    'page_title' => __( 'Ajustes de WhatsApp', 'air-light' ),
    'menu_title' => __( 'WhatsApp', 'air-light' ),
    'menu_slug'  => 'ajustes-whatsapp',
    'capability' => 'manage_options',
    'icon_url'   => 'dashicons-format-chat',
    'position'   => 80,
  ] );
} // end register_whatsapp_options_page

/**
 * The configured WhatsApp number for a channel, digits only (no "+", spaces
 * or dashes), or '' if it hasn't been set yet in Ajustes > WhatsApp.
 *
 * @param string $type 'ventas' or 'soporte'.
 * @return string
 */
function get_whatsapp_number( $type = 'ventas' ) {
  if ( ! function_exists( 'get_field' ) ) {
    return '';
  }

  $number = get_field( 'soporte' === $type ? 'whatsapp_soporte' : 'whatsapp_ventas', 'option' );

  return $number ? preg_replace( '/\D/', '', $number ) : '';
} // end get_whatsapp_number

/**
 * Builds a wa.me link for a channel, optionally with prefilled text. Falls
 * back to the bare https://wa.me/ link (WhatsApp's own "choose a chat"
 * screen) when no number is configured yet.
 *
 * @param string $type    'ventas' or 'soporte'.
 * @param string $message Optional prefilled message text.
 * @return string
 */
function get_whatsapp_url( $type = 'ventas', $message = '' ) {
  $url = 'https://wa.me/' . get_whatsapp_number( $type );

  if ( $message ) {
    $url = add_query_arg( 'text', rawurlencode( $message ), $url );
  }

  return $url;
} // end get_whatsapp_url

/**
 * Messages shown, one at a time, in the animated bubble above the floating
 * WhatsApp button (see footer.php, modules/whatsapp-float-bubble.js).
 * Editable from Ajustes > WhatsApp; returns an empty array if the "Mensajes
 * animados" toggle is off, no messages have been added yet, or ACF isn't
 * active.
 *
 * @return string[]
 */
function get_whatsapp_bubble_messages() {
  if ( ! function_exists( 'get_field' ) || ! get_field( 'whatsapp_bubble_enabled', 'option' ) ) {
    return [];
  }

  $rows = get_field( 'whatsapp_bubble_messages', 'option' );

  if ( ! $rows ) {
    return [];
  }

  return array_values( array_filter( array_map( function( $row ) {
    return trim( (string) ( $row['message'] ?? '' ) );
  }, $rows ) ) );
} // end get_whatsapp_bubble_messages
