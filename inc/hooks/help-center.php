<?php
/**
 * Centro de ayuda (help center) live search.
 *
 * The hero search field (template-parts/centro-de-ayuda/hero-search.php)
 * calls canut_help_search via AJAX (modules/help-center-canut.js) as the
 * user types. Matching is genuinely backed by the CPT + ACF data, not a
 * client-side text filter: WordPress' own title/content relevance search,
 * widened with the `palabras_clave` ACF field (inc/acf-fields/recurso-ayuda.php)
 * and the resource's categoria_ayuda term name, so a query like "garantía"
 * also surfaces resources that only mention it via category or keywords.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Resolve a search term to matching Recurso_Ayuda post IDs.
 *
 * @param string $search_term Raw, already-sanitized search string.
 * @return int[] Post IDs, title/content relevance matches first.
 */
function help_center_search_resource_ids( $search_term ) {
  global $wpdb;

  $search_term = trim( (string) $search_term );

  if ( mb_strlen( $search_term ) < 2 ) {
    return [];
  }

  // 1. Title/content match, using WordPress' own search relevance ranking.
  $title_content_query = new \WP_Query( [
    'post_type'      => 'recurso_ayuda',
    'post_status'    => 'publish',
    's'              => $search_term,
    'orderby'        => 'relevance',
    'posts_per_page' => 20,
    'fields'         => 'ids',
    'no_found_rows'  => true,
  ] );

  $like = '%' . $wpdb->esc_like( $search_term ) . '%';

  // 2. ACF "palabras_clave" keyword match - catches synonyms a customer
  // might type that aren't literally in the title/content.
  $keyword_ids = $wpdb->get_col( $wpdb->prepare(
    "SELECT p.ID FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
    WHERE p.post_type = 'recurso_ayuda' AND p.post_status = 'publish'
    AND pm.meta_key = 'palabras_clave' AND pm.meta_value LIKE %s",
    $like
  ) );

  // 3. Category name match - e.g. searching "envíos" should surface every
  // resource filed under that categoria_ayuda term, not just literal hits.
  $category_ids = $wpdb->get_col( $wpdb->prepare(
    "SELECT tr.object_id FROM {$wpdb->term_relationships} tr
    INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
    INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
    INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
    WHERE tt.taxonomy = 'categoria_ayuda' AND t.name LIKE %s
    AND p.post_type = 'recurso_ayuda' AND p.post_status = 'publish'",
    $like
  ) );

  $ids = array_unique( array_merge( $title_content_query->posts, $keyword_ids, $category_ids ) );

  return array_map( 'intval', $ids );
} // end help_center_search_resource_ids

/**
 * AJAX: live search as the visitor types in the Centro de ayuda hero field.
 * Returns matching resource IDs only - modules/help-center-canut.js shows/
 * hides the already-rendered accordion items, no markup round-trips.
 */
function help_center_search_ajax() {
  check_ajax_referer( 'canut_help_search', 'nonce' );

  $search_term = isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '';

  wp_send_json_success( [
    'ids' => help_center_search_resource_ids( $search_term ),
  ] );
} // end help_center_search_ajax
add_action( 'wp_ajax_canut_help_search', __NAMESPACE__ . '\help_center_search_ajax' );
add_action( 'wp_ajax_nopriv_canut_help_search', __NAMESPACE__ . '\help_center_search_ajax' );

/**
 * Pass the AJAX URL + nonce modules/help-center-canut.js needs. Hooked at a
 * later priority than enqueue_theme_scripts() (inc/hooks/scripts-styles.php)
 * so the 'scripts' handle already exists, only on the Centro de ayuda page.
 */
function help_center_localize_script() {
  // page-centro-de-ayuda.php applies via the page-{slug}.php template
  // hierarchy fallback (same convention as page-sistema-de-diseno.php /
  // inc/hooks/design-system.php), not an explicit Page Attributes template
  // selection - so it doesn't set the `_wp_page_template` meta
  // is_page_template() checks. Match by slug instead.
  if ( ! is_page( 'centro-de-ayuda' ) ) {
    return;
  }

  wp_localize_script( 'scripts', 'air_light_helpCenterSearch', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'canut_help_search' ),
  ] );
} // end help_center_localize_script
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\help_center_localize_script', 20 );
