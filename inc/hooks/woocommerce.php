<?php
/**
 * WooCommerce related hooks.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Show 2 products per row in the shop/archive loop, matching the CANUT
 * design system's product grid.
 *
 * The content wrapper itself (the theme's <main> markup) is handled by
 * woocommerce/global/wrapper-start.php and wrapper-end.php, not a hook.
 */
function woocommerce_loop_columns() {
  return 2;
} // end woocommerce_loop_columns

/**
 * WooCommerce core hooks woocommerce_breadcrumb() to woocommerce_before_main_content
 * (priority 20) by default, right after the wrapper opens. Both
 * woocommerce/archive-product.php and woocommerce/single-product.php call
 * woocommerce_breadcrumb() explicitly themselves at a specific spot in the
 * CANUT layout (inside the hero banner / above the gallery), so the default
 * auto-output needs removing or every page would render it twice.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

/**
 * Cart item count badge shown on the header cart icon.
 *
 * Also registered as a WooCommerce cart fragment so it updates via AJAX
 * (no full page reload) right after an add-to-cart action.
 */
function get_header_cart_count_badge() {
  $count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;

  return sprintf(
    '<span class="header-actions-canut-cart-count%s">%s</span>',
    $count ? '' : ' is-empty',
    esc_html( $count )
  );
} // end get_header_cart_count_badge

add_filter( 'woocommerce_add_to_cart_fragments', __NAMESPACE__ . '\woocommerce_cart_count_fragment' );
function woocommerce_cart_count_fragment( $fragments ) {
  $fragments['.header-actions-canut-cart-count'] = get_header_cart_count_badge();

  return $fragments;
} // end woocommerce_cart_count_fragment

/**
 * Ensure the "Tamaño" and "Capacidad" global product attributes exist, so
 * the shop-filter-bar in woocommerce/archive-product.php has real WooCommerce
 * taxonomies to filter by. This has to be a taxonomy-based (global) attribute,
 * not a custom per-product one: WooCommerce's own filter_{attribute}/
 * query_type_{attribute} query vars (read in WC_Query::get_layered_nav_chosen_attributes(),
 * applied on every product query core-side, no widget required) only match
 * against attribute taxonomies.
 *
 * Idempotent: skips any attribute/term that already exists, safe to run on
 * every request.
 */
add_action( 'init', __NAMESPACE__ . '\register_shop_filter_attributes', 20 );
function register_shop_filter_attributes() {
  if ( ! function_exists( 'wc_create_attribute' ) ) {
    return;
  }

  $attributes = [
    'tamano'    => [
      'name'  => __( 'Tamaño', 'air-light' ),
      'terms' => [ __( 'Pequeño', 'air-light' ), __( 'Mediano', 'air-light' ), __( 'Grande', 'air-light' ) ],
    ],
    'capacidad' => [
      'name'  => __( 'Capacidad', 'air-light' ),
      'terms' => [ '2.5L', '4L', '6L', '10L' ],
    ],
  ];

  $created_new_attribute = false;

  foreach ( $attributes as $slug => $attribute ) {
    if ( wc_attribute_taxonomy_id_by_name( $slug ) ) {
      continue;
    }

    wc_create_attribute( [
      'name'     => $attribute['name'],
      'slug'     => $slug,
      'type'     => 'select',
      'order_by' => 'name',
    ] );

    $created_new_attribute = true;
  }

  // Newly created attributes aren't registered as taxonomies until the next
  // init pass (WC_Post_Types::register_taxonomies() already ran at priority
  // 5, before this callback). Re-run it now so wp_insert_term() below has a
  // taxonomy to insert into within this same request.
  if ( $created_new_attribute && class_exists( '\WC_Post_Types' ) ) {
    delete_transient( 'wc_attribute_taxonomies' );
    \WC_Post_Types::register_taxonomies();
  }

  foreach ( $attributes as $slug => $attribute ) {
    $taxonomy = wc_attribute_taxonomy_name( $slug );

    if ( ! taxonomy_exists( $taxonomy ) ) {
      continue;
    }

    foreach ( $attribute['terms'] as $term_name ) {
      if ( ! term_exists( $term_name, $taxonomy ) ) {
        wp_insert_term( $term_name, $taxonomy );
      }
    }
  }
} // end register_shop_filter_attributes
