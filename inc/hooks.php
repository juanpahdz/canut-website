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
 * WhatsApp numbers (Ventas/Soporte options page + get_whatsapp_url() helper)
 */
require get_theme_file_path( 'inc/hooks/whatsapp.php' );
add_action( 'acf/init', __NAMESPACE__ . '\register_whatsapp_options_page' );

/**
 * Site-wide cookie notice (Ajustes > Cookies options page + render_cookie_notice(),
 * called from footer.php)
 */
require get_theme_file_path( 'inc/hooks/cookie-notice.php' );
add_action( 'acf/init', __NAMESPACE__ . '\register_cookie_notice_options_page' );

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
 * Only load if Advanced Custom Fields is active, so the theme doesn't break
 * without it. Registration itself is deferred to acf/init rather than run
 * immediately here - each field group calls translation functions (__())
 * inline, and doing that before WordPress's init action fires triggers WP
 * 6.7's _load_textdomain_just_in_time notice for the air-light domain.
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
  require get_theme_file_path( 'inc/acf-fields.php' );
  add_action( 'acf/init', __NAMESPACE__ . '\register_acf_field_groups' );
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
 * Product structured data (JSON-LD) extras, read from per-product fields on
 * "Página de producto CANUT" (see inc/acf-fields/product-canut.php)
 */
require get_theme_file_path( 'inc/hooks/structured-data.php' );

/**
 * Ajustes > Scripts: custom head/body/footer markup
 */
require get_theme_file_path( 'inc/hooks/custom-scripts.php' );
add_action( 'acf/init', __NAMESPACE__ . '\register_custom_scripts_options_page' );
add_action( 'wp_head', __NAMESPACE__ . '\output_custom_head_scripts', 999 );
add_action( 'wp_body_open', __NAMESPACE__ . '\output_custom_body_scripts', 999 );
add_action( 'wp_footer', __NAMESPACE__ . '\output_custom_footer_scripts', 999 );

/**
 * Checkout page (CANUT redesign)
 */
require get_theme_file_path( 'inc/hooks/checkout.php' );

/**
 * Generic checkout-wizard step-confirmation hook (Air_Light\checkout_step_continued)
 * + the data-processing consent audit log that's its first listener.
 */
require get_theme_file_path( 'inc/hooks/checkout-step-actions.php' );
require get_theme_file_path( 'inc/hooks/data-consent.php' );

if ( is_admin() ) {
  require get_theme_file_path( 'inc/admin/consent-log.php' );
}

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

/**
 * Eventos: Facebook Pixel Conversions API (Ajustes > Facebook Pixel).
 * Backend-only, one file per event - see inc/eventos/facebook-conversions-api.php.
 */
require get_theme_file_path( 'inc/eventos/facebook-conversions-api.php' );
add_action( 'acf/init', __NAMESPACE__ . '\register_facebook_pixel_options_page' );
require get_theme_file_path( 'inc/eventos/view-content.php' );
require get_theme_file_path( 'inc/eventos/add-to-cart.php' );
require get_theme_file_path( 'inc/eventos/initiate-checkout.php' );
require get_theme_file_path( 'inc/eventos/add-contact-info.php' );
require get_theme_file_path( 'inc/eventos/add-shipping-info.php' );
require get_theme_file_path( 'inc/eventos/add-payment-info.php' );
require get_theme_file_path( 'inc/eventos/initiate-payment.php' );
require get_theme_file_path( 'inc/eventos/purchase.php' );
require get_theme_file_path( 'inc/eventos/purchase-failed.php' );
require get_theme_file_path( 'inc/eventos/lead.php' );
require get_theme_file_path( 'inc/eventos/complete-registration.php' );
