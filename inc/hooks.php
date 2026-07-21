<?php
/**
 * Hooks
 *
 * All hooks that are run in the theme are listed here
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Enable search view
 */
// add_filter( 'air_helper_disable_views_search', '__return_false' );

/**
 * Breadcrumb
 */
// require get_theme_file_path( 'inc/hooks/breadcrumb.php' );

/**
 * General hooks
 */
require get_theme_file_path( 'inc/hooks/general.php' );
add_action( 'widgets_init', __NAMESPACE__ . '\widgets_init' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\historia_archive_posts_per_page' );

/**
 * Scripts and styles associated hooks
 */
require get_theme_file_path( 'inc/hooks/scripts-styles.php' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_theme_scripts' );

// NB! If you use ajax functionality in Gravity Forms, remove this line
// to prevent Uncaught ReferenceError: jQuery is not defined
add_action( 'wp_default_scripts', __NAMESPACE__ . '\move_jquery_into_footer' );

/**
 * Block styles
 */
require get_theme_file_path( 'inc/hooks/block-styles.php' );
add_action( 'init', __NAMESPACE__ . '\register_custom_block_styles' );

/**
 * Gutenberg associated hooks
 */
require get_theme_file_path( 'inc/hooks/gutenberg.php' );
add_filter( 'allowed_block_types_all', __NAMESPACE__ . '\allowed_block_types', 10, 2 );
add_filter( 'use_block_editor_for_post_type', __NAMESPACE__ . '\use_block_editor_for_post_type', 10, 2 );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\register_block_editor_assets' );
add_action( 'after_setup_theme', __NAMESPACE__ . '\setup_editor_styles' );
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\block_editor_title_input_styles' );

/**
 * ACF blocks
 */
require get_theme_file_path( 'inc/hooks/acf-blocks.php' );
add_filter( 'block_categories_all', __NAMESPACE__ . '\acf_blocks_add_category_in_gutenberg', 10, 2 );
add_action( 'acf/init', __NAMESPACE__ . '\acf_blocks_init' );
add_filter( 'acf/fields/wysiwyg/toolbars', __NAMESPACE__ . '\add_custom_tinymce_toolbars' );

/**
 * Custom ACF field groups
 *
 * Only load if Advanced Custom Fields is active, so the theme doesn't break without it
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
  require get_theme_file_path( 'inc/acf-fields.php' );
}


/**
 * Form related hooks
 */
require get_theme_file_path( 'inc/hooks/forms.php' );
add_action( 'gform_enqueue_scripts', __NAMESPACE__ . '\dequeue_gf_stylesheets', 999 );

/**
 * Internal design-system reference page (/sistema-de-diseno)
 */
require get_theme_file_path( 'inc/hooks/design-system.php' );
add_action( 'wp_head', __NAMESPACE__ . '\design_system_noindex', 1 );
add_filter( 'robots_txt', __NAMESPACE__ . '\design_system_robots_txt', 10, 2 );
add_filter( 'wp_sitemaps_posts_query_args', __NAMESPACE__ . '\design_system_exclude_from_sitemap', 10, 2 );

/**
 * WooCommerce
 */
require get_theme_file_path( 'inc/hooks/woocommerce.php' );
add_filter( 'loop_shop_columns', __NAMESPACE__ . '\woocommerce_loop_columns' );

/**
 * Checkout page (CANUT redesign)
 */
require get_theme_file_path( 'inc/hooks/checkout.php' );

/**
 * "Lleva X y ahorra Y%" per-product quantity discount
 */
require get_theme_file_path( 'inc/hooks/quantity-discount.php' );

/**
 * Cart drawer (replaces the standalone Cart page)
 */
require get_theme_file_path( 'inc/hooks/cart-drawer.php' );

/**
 * Centro de ayuda (help center) live search
 */
require get_theme_file_path( 'inc/hooks/help-center.php' );

/**
 * Soporte: community question form + approval workflow
 */
require get_theme_file_path( 'inc/hooks/soporte.php' );
