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
