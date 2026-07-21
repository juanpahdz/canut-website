<?php
/**
 * Gather all bits and pieces together.
 * If you end up having multiple post types, taxonomies,
 * hooks and functions - please split those to their
 * own files under /inc and just require here.
 *
 * @Date: 2019-10-15 12:30:02
 * @Last Modified by:   Roni Laukkarinen
 * @Last Modified time: 2024-01-10 18:54:48
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * The current version of the theme.
 */
define( 'AIR_LIGHT_VERSION', '10.2.0' );

// We need to have some defaults as comments or empties so let's allow this:
// phpcs:disable Squiz.Commenting.InlineComment.SpacingBefore, WordPress.Arrays.ArrayDeclarationSpacing.SpaceInEmptyArray

/**
 * Theme settings
 */
add_action( 'after_setup_theme', function() {
  $theme_settings = [
    /**
     * Theme textdomain
     */
    'textdomain' => 'air-light',

    /**
     * Content width
     */
    'content_width' => 800,

    /**
     * Logo and featured image
     */
    'default_featured_image'  => null,
    'logo'                    => '/assets/svg/logo.svg',

    /**
     * Custom setting group settings when using Air setting groups plugin.
     * On multilingual sites using Polylang, translations are handled automatically.
     */
    'custom_settings' => [
      // 'your-custom-setting' => [
      //   'id' => Your custom setting post id,
      //   'title' => 'Your custom setting',
      //   'block-editor' => true,
      //  ],
    ],

    'social_media_accounts'  => [
      // 'twitter' => [
      //   'title' => 'Twitter',
      //   'url'   => 'https://twitter.com/digitoimistodude',
      // ],
    ],

    /**
     * All links are checked with JS, if those direct to external site and if,
     * indicator of that is included. Exclude domains from that check in this array.
     */
    'external_link_domains_exclude' => [
      'localhost:3000',
      'airdev.test',
      'airwptheme.com',
      'localhost',
    ],

    /**
     * Menu locations
     */
    'menu_locations' => [
      // Not run through __() - these are wp-admin-only location names (Appearance
      // > Menus), and this array is built on after_setup_theme, before init;
      // translating this early trips WP 6.7's "textdomain loaded too early" notice.
      'primary'        => 'Primary Menu',
      'footer_company' => 'Footer - Compañía',
      'footer_support' => 'Footer - Soporte',
    ],

    /**
     * Taxonomies
     *
     * See the instructions:
     * https://github.com/digitoimistodude/air-light#custom-taxonomies
     */
    'taxonomies' => [
      // 'Your_Taxonomy' => [ 'post', 'page' ],
      'Categoria_Ayuda' => [ 'recurso_ayuda', 'pregunta_soporte' ],
    ],

    /**
     * Post types
     *
     * See the instructions:
     * https://github.com/digitoimistodude/air-light#custom-post-types
     */
    'post_types' => [
      'Historia',
      'Recurso_Ayuda',
      'Pregunta_Soporte',
    ],

    /**
     * Gutenberg -related settings
     */
    // Register custom ACF Blocks
    'acf_blocks' => [
      [
        'name'  => 'homepage-canut',
        // Not run through __() - same after_setup_theme/init ordering issue as
        // menu_locations above; this is a block-editor-only label.
        'title' => 'Página de inicio CANUT',
        // One block holds every homepage section (hero, banda de confianza,
        // estilo de vida, producto destacado, cómo funciona, branding
        // emocional, reseñas, garantía, CTA final) so editing the Front
        // page means editing this single block's fields.
        'icon'  => 'admin-home',
      ],
      [
        'name'  => 'nosotros-canut',
        'title' => 'Página Nosotros CANUT',
        // One block holds every "Nosotros" section (hero, origen,
        // diferenciales, CTA final) so editing the page means editing
        // this single block's fields.
        'icon'  => 'admin-users',
      ],
      [
        'name'  => 'contacto-canut',
        'title' => 'Página Contacto CANUT',
        // One block holds every "Contacto" section (hero, formulario,
        // WhatsApp, atención/correo, ubicación) so editing the page means
        // editing this single block's fields.
        'icon'  => 'email-alt',
        // The form shows a success/error banner from $_GET after submit,
        // so this block's output can't be cached like a static section.
        'prevent_cache' => true,
      ],
      [
        'name'  => 'garantia-canut',
        'title' => 'Página Garantía CANUT',
        // One block holds every "Garantía" section (hero, artículo legal,
        // exclusiones, confianza + CTA) so editing the page means editing
        // this single block's fields.
        'icon'  => 'shield',
        // Overrides acf_block_defaults' 'preview' mode: always shows the
        // ACF fields form straight away instead of the rendered page
        // preview + an "Edit" toolbar toggle, since editors kept missing
        // that toggle on this block.
        'mode'  => 'edit',
      ],
    ],

    // Custom ACF block default settings
    'acf_block_defaults' => [
      'category'          => 'air-light',
      'mode'              => 'preview',
      'align'             => 'full',
      'post_types'        => [
        'page',
      ],
      'supports'  => [
        'align'           => false,
        'anchor'          => true,
        'customClassName' => false,
      ],
      'validate'          => true,
      'render_callback'   => __NAMESPACE__ . '\render_acf_block',
    ],

    // Restrict to only selected blocks
    //
    // Options: 'none', 'all', 'all-core-blocks', 'all-acf-blocks',
    // or any specific block or a combination of these
    // Accepts both string (all*/none-options only) and array (options + specific blocks)
    'allowed_blocks' => [
      'post' => [
        'core/column',
        'core/columns',
        'core/coverImage',
        'core/embed',
        'core/freeform',
        'core/gallery',
        'core/heading',
        'core/html',
        'core/image',
        'core/list',
        'core/list-item',
        'core/paragraph',
        'core/quote',
        'core/block',
        'core/table',
        'core/textColumns',
      ],
      'page' => [
        'all',
      ],
    ],

    // If you want to use classic editor somewhere, define it here
    'use_classic_editor' => [],

    // Add your own settings and use them wherever you need, for example THEME_SETTINGS['my_custom_setting']
    'my_custom_setting' => true,
  ];

  $theme_settings = apply_filters( 'air_light_theme_settings', $theme_settings );

  define( 'THEME_SETTINGS', $theme_settings );
} ); // end action after_setup_theme

/**
 * Debug function to print all available blocks
 */
function debug_print_all_blocks() {
  $blocks = \WP_Block_Type_Registry::get_instance()->get_all_registered();
  $block_names = array_map(function( $block ) {
    return "'" . $block->name . "',";
  }, $blocks);
  echo '<pre>' . implode( "\n", $block_names ) . '</pre>'; // phpcs:ignore
  die();
}

// Uncomment the following line to see all available blocks:
// add_action( 'init', __NAMESPACE__ . '\debug_print_all_blocks' );


/**
 * Required files
 */
require get_theme_file_path( '/inc/hooks.php' );
require get_theme_file_path( '/inc/includes.php' );
require get_theme_file_path( '/inc/template-tags.php' );

// Run theme setup
add_action( 'after_setup_theme', __NAMESPACE__ . '\theme_setup' );
add_action( 'after_setup_theme', __NAMESPACE__ . '\build_theme_support' );

/*
 * First: we register the taxonomies and post types after setup theme
 * If air-helper loads (for translations), we unregister the original taxonomies and post types
 * and reregister them with the translated ones.
 *
 * This allows the slugs translations to work before the translations are available,
 * and for the label translations to work if they are available.
 */
add_action( 'after_setup_theme', __NAMESPACE__ . '\build_taxonomies' );
add_action( 'after_setup_theme', __NAMESPACE__ . '\build_post_types' );

add_action( 'after_air_helper_init', __NAMESPACE__ . '\rebuild_taxonomies' );
add_action( 'after_air_helper_init', __NAMESPACE__ . '\rebuild_post_types' );
