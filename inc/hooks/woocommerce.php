<?php
/**
 * WooCommerce related hooks.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * WooCommerce enqueues its own default frontend stylesheet (woocommerce.css -
 * generic list/table/button/notice styling meant for themes that don't build
 * their own) on every WC-related page. It was never actually relevant before
 * on the checkout page specifically (the Checkout block used its own,
 * separate plugin stylesheet, not this legacy one) - now that checkout
 * renders classic templates (woocommerce/checkout/*.php), those generic
 * defaults (payment method list background/spacing, coupon form, etc.) do
 * apply and fight the CANUT design system's own components
 * (components/_payment-option-canut.scss, _form-canut.scss, _button-canut.scss,
 * _banner-canut.scss - all styled to stand alone). Every WooCommerce template
 * on this site already has a CANUT-styled equivalent, so this is dequeued
 * everywhere rather than patched page by page.
 *
 * @return string[]
 */
function woocommerce_disable_default_styles() {
  return [];
} // end woocommerce_disable_default_styles
add_filter( 'woocommerce_enqueue_styles', __NAMESPACE__ . '\woocommerce_disable_default_styles' );

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
 * WooCommerce core also hooks its own default woocommerce_order_details_table()
 * (product table + totals + billing/shipping address, via order/order-details.php
 * and order/order-details-customer.php) to woocommerce_thankyou at priority 10 -
 * fired a second time by our own woocommerce/checkout/thankyou.php (kept for
 * gateway/plugin compatibility), duplicating the CANUT-styled order summary
 * template-parts/order/success.php already renders itself. Only removed from
 * this hook - the same callback stays on woocommerce_view_order (My Account >
 * Orders > View), which has no custom replacement.
 */
remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );

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

/**
 * "Reintentar pago"/"Cambiar método de pago" on template-parts/order/failed.php
 * both link to $order->get_checkout_payment_url() - WooCommerce's own
 * order-pay endpoint refuses to render anything for it unless
 * WC_Order::needs_payment() says so, which checks the order's status against
 * this filter. Core's own default is ['pending', 'failed'] only, so a
 * 'cancelled' or 'voided' order hits order_pay()'s own
 * "This order's status is ... it cannot be paid for" exception instead of the
 * payment form - exactly what shows up as "El estado de este pedido es
 * «Cancelado». No se ha podido pagar." on that page.
 *
 * The Wompi webhook handler (wompi-portal-de-pagos/includes/class-wompi-portal-pagos-webhook-handler.php)
 * sets 'cancelled' on a DECLINED transaction and its own custom 'voided'
 * status on a VOIDED one - neither ever actually captured a charge, so both
 * are legitimate to retry. 'refunded' is deliberately left out: that status
 * means a charge DID succeed at some point, and letting a customer "retry
 * payment" on it risks a second, confusing charge instead.
 *
 * @param string[]  $statuses Statuses eligible for payment, without the 'wc-' prefix.
 * @param \WC_Order $order    Order being checked.
 * @return string[]
 */
add_filter( 'woocommerce_valid_order_statuses_for_payment', __NAMESPACE__ . '\woocommerce_allow_pay_for_declined_orders', 10, 2 );
function woocommerce_allow_pay_for_declined_orders( $statuses, $order ) {
  $statuses[] = 'cancelled';
  $statuses[] = 'voided';

  return $statuses;
} // end woocommerce_allow_pay_for_declined_orders

/**
 * WooCommerce's own "Product gallery" admin metabox
 * (WC_Meta_Box_Product_Images::output()) renders each gallery attachment
 * with wp_get_attachment_image( $id, 'thumbnail' ) to build its preview
 * list, and rewrites `_product_image_gallery` on every metabox render to
 * drop any attachment that call returns empty for. A video attachment has
 * no image representation unless it was given a poster frame, so
 * without this filter every video added to the gallery (see
 * woocommerce/single-product.php, which already renders gallery videos on
 * the frontend) silently vanishes the moment the product is saved.
 *
 * Fall back to the generic mime-type icon core itself uses for
 * wp_get_attachment_image_src( $id, $size, $icon = true ) - a fallback
 * WooCommerce's metabox never asks for - so the src comes back non-empty
 * and the attachment survives the save. Scoped to admin only; the frontend
 * gallery template deliberately keeps rendering real <video> markup.
 */
add_filter( 'wp_get_attachment_image_src', __NAMESPACE__ . '\admin_video_gallery_thumbnail_fallback', 10, 4 );
function admin_video_gallery_thumbnail_fallback( $image, $attachment_id, $size, $icon ) {
  if ( $image || $icon || ! is_admin() ) {
    return $image;
  }

  $mime_type = get_post_mime_type( $attachment_id );

  if ( ! $mime_type || ! str_starts_with( $mime_type, 'video/' ) ) {
    return $image;
  }

  $icon_src = wp_mime_type_icon( $attachment_id, '.svg' );

  return $icon_src ? [ $icon_src, 48, 64, false ] : $image;
} // end admin_video_gallery_thumbnail_fallback
