<?php
/**
 * Internal design-system reference page hooks (/sistema-de-diseno).
 *
 * The page itself is a regular WordPress Page (slug: sistema-de-diseno)
 * rendered by page-sistema-de-diseno.php. It must never be indexed, listed
 * in the sitemap, or linked from public navigation.
 *
 * @package air-light
 */

namespace Air_Light;

const DESIGN_SYSTEM_SLUG = 'sistema-de-diseno';

/**
 * Output a noindex, nofollow meta tag on the design-system page.
 */
function design_system_noindex() {
  if ( is_page( DESIGN_SYSTEM_SLUG ) ) {
    echo '<meta name="robots" content="noindex, nofollow">' . "\n";
  }
} // end design_system_noindex

/**
 * Add a Disallow rule for the design-system page to the virtual robots.txt.
 *
 * @param string $output Current robots.txt content.
 * @param bool   $public Whether the site is public.
 * @return string
 */
function design_system_robots_txt( $output, $public ) {
  if ( $public ) {
    $output .= 'Disallow: /' . DESIGN_SYSTEM_SLUG . "\n";
  }

  return $output;
} // end design_system_robots_txt

/**
 * Exclude the design-system page from WordPress core XML sitemaps.
 *
 * @param array  $args      WP_Query args for the sitemap.
 * @param string $post_type Post type being queried.
 * @return array
 */
function design_system_exclude_from_sitemap( $args, $post_type ) {
  if ( 'page' !== $post_type ) {
    return $args;
  }

  $page = get_page_by_path( DESIGN_SYSTEM_SLUG );

  if ( $page ) {
    $args['post__not_in'] = array_merge( $args['post__not_in'] ?? [], [ $page->ID ] );
  }

  return $args;
} // end design_system_exclude_from_sitemap
