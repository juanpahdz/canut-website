<?php
/**
 * Historia: shared query/render helpers, the public "Cuenta tu historia"
 * submission form (Cloudflare Turnstile-gated, lands as a draft for
 * editorial review - same admin-post.php convention as the Soporte question
 * form, see inc/hooks/soporte.php), and a one-off WP-CLI migration of the
 * legacy per-product "reviews" ACF repeater (inc/acf-fields/product-canut.php)
 * into real Historia posts linked via the `product` field.
 *
 * `Historia` is now the single source of truth for both the general
 * historias archive/homepage and each product's "Nuestros Clientes" section
 * - a story optionally links to one product (the `product` ACF field, see
 * inc/acf-fields/historia.php) to appear there too.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Registers the "Ajustes de Historias" ACF options page (Turnstile
 * credentials). Deferred to acf/init like the theme's other options pages,
 * see inc/hooks.php, and no-ops if ACF Pro isn't active.
 */
function register_historia_options_page() {
  if ( ! function_exists( 'acf_add_options_page' ) ) {
    return;
  }

  acf_add_options_page( [
    'page_title' => __( 'Ajustes de Historias', 'air-light' ),
    'menu_title' => __( 'Historias', 'air-light' ),
    'menu_slug'  => 'ajustes-historias',
    'capability' => 'manage_options',
    'icon_url'   => 'dashicons-format-quote',
    'position'   => 82,
  ] );
} // end register_historia_options_page

/**
 * Turnstile settings from Ajustes > Historias, cached for the rest of the request.
 *
 * @return array{enabled: bool, site_key: string, secret_key: string}
 */
function historia_turnstile_settings() {
  static $settings = null;

  if ( null !== $settings ) {
    return $settings;
  }

  if ( ! function_exists( 'get_field' ) ) {
    $settings = [
      'enabled'    => false,
      'site_key'   => '',
      'secret_key' => '',
    ];

    return $settings;
  }

  $settings = [
    'enabled'    => (bool) get_field( 'turnstile_enabled', 'option' ),
    'site_key'   => trim( (string) get_field( 'turnstile_site_key', 'option' ) ),
    'secret_key' => trim( (string) get_field( 'turnstile_secret_key', 'option' ) ),
  ];

  return $settings;
} // end historia_turnstile_settings

/**
 * Whether Turnstile is actually usable: toggle on, plus both keys configured.
 *
 * @return bool
 */
function historia_turnstile_is_configured() {
  $settings = historia_turnstile_settings();

  return $settings['enabled'] && $settings['site_key'] && $settings['secret_key'];
} // end historia_turnstile_is_configured

/**
 * Verifies a Turnstile response token server-side.
 *
 * @see https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
 * @param string $token The `cf-turnstile-response` field submitted with the form.
 * @return bool
 */
function historia_verify_turnstile( $token ) {
  if ( ! $token ) {
    return false;
  }

  $settings = historia_turnstile_settings();

  $response = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', [
    'timeout' => 8,
    'body'    => [
      'secret'   => $settings['secret_key'],
      'response' => $token,
      'remoteip' => get_client_ip_address(),
    ],
  ] );

  if ( is_wp_error( $response ) ) {
    return false;
  }

  $body = json_decode( wp_remote_retrieve_body( $response ), true );

  return ! empty( $body['success'] );
} // end historia_verify_turnstile

/**
 * Builds the $args shape template-parts/historia/card.php expects
 * (image/rating/quote/author) for one Historia post. Shared by
 * archive-historia.php, single-historia.php and the homepage's random
 * selection - those three used to build this array inline, identically.
 *
 * @param int $historia_id Historia post ID.
 * @return array{image: array, rating: int, quote: string, author: string}
 */
function historia_get_card_args( $historia_id ) {
  return [
    'image'  => [
      'url' => get_the_post_thumbnail_url( $historia_id, 'medium' ),
      'alt' => get_the_title( $historia_id ),
    ],
    'rating' => (int) ( get_field( 'rating', $historia_id ) ?: 5 ),
    'quote'  => get_field( 'quote', $historia_id ),
    'author' => get_the_title( $historia_id ),
  ];
} // end historia_get_card_args

/**
 * Random, well-rated published Historia posts for the homepage - replaces
 * the old `is_featured` checkbox curation entirely.
 *
 * @param int $limit     How many to return.
 * @param int $min_rating Minimum `rating` (1-5) to be eligible.
 * @return \WP_Query
 */
function historia_query_random_for_homepage( $limit = 5, $min_rating = 4 ) {
  return new \WP_Query( [
    'post_type'      => 'historia',
    'post_status'    => 'publish',
    'posts_per_page' => $limit,
    'orderby'        => 'rand',
    'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
      [
        'key'     => 'rating',
        'value'   => $min_rating,
        'compare' => '>=',
        'type'    => 'NUMERIC',
      ],
    ],
  ] );
} // end historia_query_random_for_homepage

/**
 * Published Historia posts linked to a given product, best-rated first.
 * Returns the same shape the old `reviews` ACF repeater on the product used
 * to (`name`/`text`/`image`), plus `rating`, so the product template's
 * render loop barely changes.
 *
 * @param int $product_id WooCommerce product ID.
 * @param int $limit      Max stories to return.
 * @return array<int, array{name: string, text: string, image: string, rating: int}>
 */
function historia_query_for_product( $product_id, $limit = 6 ) {
  $query = new \WP_Query( [
    'post_type'      => 'historia',
    'post_status'    => 'publish',
    'posts_per_page' => $limit,
    'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
      'relation'       => 'AND',
      'product_clause' => [
        'key'     => 'product',
        'value'   => $product_id,
        'compare' => '=',
      ],
      'rating_clause'  => [
        'key'     => 'rating',
        'type'    => 'NUMERIC',
        'compare' => 'EXISTS',
      ],
    ],
    'orderby'        => [
      'rating_clause' => 'DESC',
      'date'          => 'DESC',
    ],
  ] );

  $reviews = [];

  foreach ( $query->posts as $historia ) {
    $reviews[] = [
      'name'   => get_the_title( $historia ),
      'text'   => get_field( 'quote', $historia->ID ),
      'image'  => get_the_post_thumbnail_url( $historia->ID, 'medium' ) ?: '',
      'rating' => (int) ( get_field( 'rating', $historia->ID ) ?: 5 ),
    ];
  }

  return $reviews;
} // end historia_query_for_product

/**
 * Average rating + formatted count across ALL published Historia posts
 * linked to a product (not just the ones actually displayed), for the
 * "Nuestros Clientes" section header. Always computed from real data - no
 * placeholder score. Returns null when the product has no linked stories
 * yet, so the template can show an empty state instead of a fake number.
 *
 * @param int $product_id WooCommerce product ID.
 * @return array{score: string, score_rounded: int, count: string}|null
 */
function historia_rating_summary_for_product( $product_id ) {
  $ids = get_posts( [
    'post_type'      => 'historia',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'fields'         => 'ids',
    'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
      [
        'key'     => 'product',
        'value'   => $product_id,
        'compare' => '=',
      ],
    ],
  ] );

  if ( ! $ids ) {
    return null;
  }

  $total = 0;
  foreach ( $ids as $id ) {
    $total += (int) ( get_field( 'rating', $id ) ?: 5 );
  }

  $average = $total / count( $ids );

  return [
    'score'         => number_format_i18n( $average, 1 ),
    'score_rounded' => (int) round( $average ),
    /* translators: %d: number of stories linked to this product. */
    'count'         => sprintf( _n( '(%d reseña)', '(%d reseñas)', count( $ids ), 'air-light' ), count( $ids ) ),
  ];
} // end historia_rating_summary_for_product

/**
 * Handles the public "Cuenta tu historia" form
 * (template-parts/historia/submit-form.php) - same admin-post.php
 * nonce+honeypot convention as soporte_form_submit()
 * (inc/hooks/soporte.php), plus a Turnstile check. Creates a `draft`
 * Historia post (not `pending` like Soporte - the client wants these to
 * show up as an editable draft, not the pending-review queue) for an editor
 * to review and publish manually.
 */
function historia_form_submit() {
  $redirect_url = ! empty( $_POST['canut_historia_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['canut_historia_redirect'] ) ) : home_url( '/' );

  if ( ! isset( $_POST['canut_historia_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['canut_historia_nonce'] ), 'canut_historia_form' ) ) {
    wp_safe_redirect( add_query_arg( 'historia', 'error', $redirect_url ) );
    exit;
  }

  // Honeypot: hidden from real visitors via CSS, only bots fill it in.
  if ( ! empty( $_POST['canut_historia_hp'] ) ) {
    wp_safe_redirect( $redirect_url );
    exit;
  }

  if ( historia_turnstile_is_configured() ) {
    $token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';

    if ( ! historia_verify_turnstile( $token ) ) {
      wp_safe_redirect( add_query_arg( 'historia', 'error', $redirect_url ) );
      exit;
    }
  }

  $nombre     = isset( $_POST['canut_historia_nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['canut_historia_nombre'] ) ) : '';
  $rating     = isset( $_POST['canut_historia_rating'] ) ? absint( $_POST['canut_historia_rating'] ) : 0;
  $quote      = isset( $_POST['canut_historia_quote'] ) ? sanitize_textarea_field( wp_unslash( $_POST['canut_historia_quote'] ) ) : '';
  $product_id = isset( $_POST['canut_historia_product'] ) ? absint( $_POST['canut_historia_product'] ) : 0;
  $order_id   = isset( $_POST['canut_historia_order_id'] ) ? absint( preg_replace( '/\D/', '', wp_unslash( $_POST['canut_historia_order_id'] ) ) ) : 0;

  if ( ! $nombre || ! $quote || $rating < 1 || $rating > 5 || ! $order_id ) {
    wp_safe_redirect( add_query_arg( 'historia', 'error', $redirect_url ) );
    exit;
  }

  // Verified-purchase gate: only a real WooCommerce order lets a story through.
  $order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : false;

  if ( ! $order instanceof \WC_Order ) {
    wp_safe_redirect( add_query_arg( 'historia', 'error', $redirect_url ) );
    exit;
  }

  if ( $product_id && 'product' !== get_post_type( $product_id ) ) {
    $product_id = 0;
  }

  $post_id = wp_insert_post( [
    'post_type'   => 'historia',
    'post_status' => 'draft',
    'post_title'  => $nombre,
  ], true );

  if ( is_wp_error( $post_id ) ) {
    wp_safe_redirect( add_query_arg( 'historia', 'error', $redirect_url ) );
    exit;
  }

  update_field( 'quote', $quote, $post_id );
  update_field( 'rating', $rating, $post_id );
  update_field( 'order_id', $order_id, $post_id );

  if ( $product_id ) {
    update_field( 'product', $product_id, $post_id );
  }

  if ( ! empty( $_FILES['canut_historia_foto']['name'] ) ) {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $attachment_id = media_handle_upload( 'canut_historia_foto', $post_id );

    if ( ! is_wp_error( $attachment_id ) ) {
      set_post_thumbnail( $post_id, $attachment_id );
    }
  }

  historia_notify_admin_of_new_story( $post_id );

  wp_safe_redirect( add_query_arg( 'historia', 'exito', $redirect_url ) );
  exit;
} // end historia_form_submit
add_action( 'admin_post_canut_historia_submit', __NAMESPACE__ . '\historia_form_submit' );
add_action( 'admin_post_nopriv_canut_historia_submit', __NAMESPACE__ . '\historia_form_submit' );

/**
 * Email the site admin so a new draft story doesn't sit unnoticed - mirrors
 * soporte_notify_admin_of_new_question() (inc/hooks/soporte.php).
 *
 * @param int $post_id The new Historia post ID.
 */
function historia_notify_admin_of_new_story( $post_id ) {
  $edit_link = get_edit_post_link( $post_id, 'raw' );

  wp_mail(
    get_option( 'admin_email' ),
    __( 'Nueva historia enviada, pendiente de revisión', 'air-light' ),
    sprintf(
      /* translators: %s: link to review/publish the story in wp-admin */
      __( "Se envió una nueva historia desde el sitio.\n\nRevísala y publícala para que aparezca en el sitio:\n%s", 'air-light' ),
      $edit_link
    )
  );
} // end historia_notify_admin_of_new_story

/**
 * One-off migration: creates a real, published Historia post (linked to its
 * product via the `product` field) for every row of the legacy per-product
 * `reviews` ACF repeater (inc/acf-fields/product-canut.php), then removes
 * that repeater's data from the product. The old repeater never captured a
 * rating, so migrated stories default to 5. Idempotent via a
 * `_historia_reviews_migrated` product-meta flag, so it's safe to run more
 * than once.
 *
 * WP-CLI only (`wp canut migrate-historia-reviews`) - a one-off data
 * migration, not a feature that needs to run on every request.
 */
function historia_migrate_product_reviews() {
  $products = get_posts( [
    'post_type'      => 'product',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'fields'         => 'ids',
  ] );

  $migrated_count = 0;
  $skipped_count  = 0;

  foreach ( $products as $product_id ) {
    if ( get_post_meta( $product_id, '_historia_reviews_migrated', true ) ) {
      $skipped_count++;
      \WP_CLI::log( sprintf( 'Skipped product #%d (already migrated).', $product_id ) );
      continue;
    }

    if ( ! have_rows( 'reviews', $product_id ) ) {
      update_post_meta( $product_id, '_historia_reviews_migrated', 1 );
      continue;
    }

    while ( have_rows( 'reviews', $product_id ) ) {
      the_row();

      $name  = get_sub_field( 'name' );
      $text  = get_sub_field( 'text' );
      $image = get_sub_field( 'image' );

      if ( ! $name || ! $text ) {
        continue;
      }

      $historia_id = wp_insert_post( [
        'post_type'   => 'historia',
        'post_status' => 'publish',
        'post_title'  => $name,
      ], true );

      if ( is_wp_error( $historia_id ) ) {
        \WP_CLI::warning( sprintf( 'Failed to migrate a review for product #%d: %s', $product_id, $historia_id->get_error_message() ) );
        continue;
      }

      update_field( 'quote', $text, $historia_id );
      update_field( 'rating', 5, $historia_id );
      update_field( 'product', $product_id, $historia_id );

      if ( ! empty( $image['ID'] ) ) {
        set_post_thumbnail( $historia_id, $image['ID'] );
      }

      $migrated_count++;
    }

    delete_post_meta( $product_id, 'reviews' );
    delete_post_meta( $product_id, 'reviews_rating_score' );
    delete_post_meta( $product_id, 'reviews_rating_count' );
    update_post_meta( $product_id, '_historia_reviews_migrated', 1 );

    \WP_CLI::log( sprintf( 'Migrated product #%d.', $product_id ) );
  }

  \WP_CLI::success( sprintf( '%d review(s) migrated into Historia posts, %d product(s) already migrated (skipped).', $migrated_count, $skipped_count ) );
} // end historia_migrate_product_reviews

if ( defined( 'WP_CLI' ) && WP_CLI ) {
  \WP_CLI::add_command( 'canut migrate-historia-reviews', __NAMESPACE__ . '\historia_migrate_product_reviews' );
}
