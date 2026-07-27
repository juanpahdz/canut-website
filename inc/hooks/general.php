<?php
/**
 * General hooks.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function widgets_init() {
  register_sidebar( array(
    'name'          => esc_html__( 'Sidebar', 'air-light' ),
    'id'            => 'sidebar-1',
    'description'   => esc_html__( 'Add widgets here.', 'air-light' ),
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h2 class="widget-title">',
    'after_title'   => '</h2>',
  ) );
} // end widgets_init

/**
 * Cap how many historias load per page on the historia archive, since there
 * can be thousands of them - the rest are reached via pagination.
 *
 * @param \WP_Query $query The query being modified.
 */
function historia_archive_posts_per_page( $query ) {
  if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'historia' ) ) {
    $query->set( 'posts_per_page', 24 );
  }
} // end historia_archive_posts_per_page

/**
 * Visitor's real IP address, preferring the client-facing header set by
 * whatever's in front of PHP (CDN/load balancer) over $_SERVER['REMOTE_ADDR'],
 * which on this stack is the proxy, not the visitor. Shared by anything that
 * needs to log a real visitor IP - the Facebook Conversions API events
 * (inc/eventos/facebook-conversions-api.php) and the data-processing consent
 * log (inc/hooks/data-consent.php) both call this instead of each keeping
 * their own copy.
 *
 * @return string
 */
function get_client_ip_address() {
  foreach ( [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ] as $key ) {
    if ( empty( $_SERVER[ $key ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
      continue;
    }

    $ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
    $ip = trim( explode( ',', $ip )[0] );

    if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
      return $ip;
    }
  }

  return '';
} // end get_client_ip_address
