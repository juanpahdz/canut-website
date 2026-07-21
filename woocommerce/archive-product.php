<?php
/**
 * Shop / category archive page.
 *
 * Overrides WooCommerce's default archive-product.php. The header/footer are
 * the theme's own; the product loop still relies on WooCommerce's main query
 * (have_posts()/the_post()), rendered through woocommerce/content-product.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

get_header();

$queried_term = null;

if ( is_product_category() || is_product_tag() ) {
  $queried_term = get_queried_object();
}

$banner_title = woocommerce_page_title( false );
$banner_text  = $queried_term && $queried_term->description
  ? wp_strip_all_tags( $queried_term->description )
  : __( 'Tecnología de precisión para el bienestar de tu mascota.', 'air-light' );

$banner_image = null;

if ( $queried_term ) {
  $thumbnail_id = get_term_meta( $queried_term->term_id, 'thumbnail_id', true );

  if ( $thumbnail_id ) {
    $banner_image = wp_get_attachment_image_url( $thumbnail_id, 'full' );
  }
}

if ( ! $banner_image ) {
  $banner_image = get_theme_file_uri( 'assets/images/homepage/hero-dog-feeder.jpg' );
}

$columns = apply_filters( 'loop_shop_columns', 2 );

do_action( 'woocommerce_before_main_content' );

/**
 * Currently chosen filter_{taxonomy} terms, keyed by taxonomy, read the same
 * way WC_Query::get_layered_nav_chosen_attributes() does (comma-separated
 * term slugs). Used only to pre-check the right boxes below; the actual
 * filtering already happened core-side by the time this template runs.
 */
$chosen_filters = [];

foreach ( [ 'pa_tamano', 'pa_capacidad' ] as $taxonomy ) {
  $attribute_name = str_replace( 'pa_', '', $taxonomy );
  $value          = isset( $_GET[ 'filter_' . $attribute_name ] ) ? wc_clean( wp_unslash( $_GET[ 'filter_' . $attribute_name ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

  $chosen_filters[ $taxonomy ] = $value ? explode( ',', $value ) : [];
}

$min_price = isset( $_GET['min_price'] ) ? wc_clean( wp_unslash( $_GET['min_price'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$max_price = isset( $_GET['max_price'] ) ? wc_clean( wp_unslash( $_GET['max_price'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$has_active_filters = array_filter( $chosen_filters ) || '' !== $min_price || '' !== $max_price;
$archive_url         = $queried_term ? get_term_link( $queried_term ) : get_permalink( wc_get_page_id( 'shop' ) );

/**
 * Render one attribute filter dropdown: a <details> disclosure holding a
 * checkbox per term of the given taxonomy, scoped to terms that actually
 * have a matching product in the current query. Submits via a hidden,
 * comma-joined input (modules/shop-filters.js keeps it in sync with the
 * checkboxes) since WC only reads filter_{attribute} as a single string,
 * never as an array of values.
 */
$render_attribute_filter = function ( $taxonomy, $label ) use ( $chosen_filters ) {
  $attribute_name = str_replace( 'pa_', '', $taxonomy );
  $terms          = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => true ] );

  if ( empty( $terms ) || is_wp_error( $terms ) ) {
    return;
  }

  $chosen = $chosen_filters[ $taxonomy ];
  ?>
  <details class="shop-filter-bar-dropdown" data-shop-filter>
    <summary class="shop-filter-bar-btn">
      <?php echo esc_html( $label ); ?>
      <?php if ( $chosen ) : ?>
        <span class="shop-filter-bar-badge"><?php echo esc_html( count( $chosen ) ); ?></span>
      <?php endif; ?>
      <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
    </summary>
    <div class="shop-filter-bar-panel">
      <?php foreach ( $terms as $term ) : ?>
        <label class="shop-filter-bar-option">
          <input
            type="checkbox"
            value="<?php echo esc_attr( $term->slug ); ?>"
            data-filter-taxonomy="<?php echo esc_attr( $attribute_name ); ?>"
            <?php checked( in_array( $term->slug, $chosen, true ) ); ?>
          >
          <?php echo esc_html( $term->name ); ?>
        </label>
      <?php endforeach; ?>
      <input type="hidden" name="filter_<?php echo esc_attr( $attribute_name ); ?>" value="<?php echo esc_attr( implode( ',', $chosen ) ); ?>" data-filter-hidden="<?php echo esc_attr( $attribute_name ); ?>">
      <input type="hidden" name="query_type_<?php echo esc_attr( $attribute_name ); ?>" value="or">
      <button type="submit" class="button-canut-base button-canut-primary is-full-width is-no-arrow shop-filter-bar-apply"><?php esc_html_e( 'Aplicar', 'air-light' ); ?></button>
    </div>
  </details>
  <?php
};

?>

<div class="shop-hero" style="background-image:url('<?php echo esc_url( $banner_image ); ?>');">
  <div class="wrap-canut shop-hero-content">
    <?php woocommerce_breadcrumb(); ?>
    <h1 class="shop-hero-title"><?php echo esc_html( $banner_title ); ?></h1>
    <p class="shop-hero-subtitle"><?php echo esc_html( $banner_text ); ?></p>
  </div>
</div>

<div class="shop-filter-bar">
  <div class="wrap-canut shop-filter-bar-inner">
    <form method="get" action="<?php echo esc_url( $archive_url ); ?>" class="shop-filter-bar-filters" data-shop-filter-bar>
      <?php
      $render_attribute_filter( 'pa_tamano', __( 'Tamaño', 'air-light' ) );
      $render_attribute_filter( 'pa_capacidad', __( 'Capacidad', 'air-light' ) );
      ?>
      <details class="shop-filter-bar-dropdown" data-shop-filter>
        <summary class="shop-filter-bar-btn">
          <?php esc_html_e( 'Precio', 'air-light' ); ?>
          <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
        </summary>
        <div class="shop-filter-bar-panel shop-filter-bar-panel-price">
          <label class="shop-filter-bar-price-field">
            <?php esc_html_e( 'Mínimo', 'air-light' ); ?>
            <input type="number" name="min_price" min="0" step="0.01" value="<?php echo esc_attr( $min_price ); ?>">
          </label>
          <label class="shop-filter-bar-price-field">
            <?php esc_html_e( 'Máximo', 'air-light' ); ?>
            <input type="number" name="max_price" min="0" step="0.01" value="<?php echo esc_attr( $max_price ); ?>">
          </label>
          <button type="submit" class="button-canut-base button-canut-primary is-full-width is-no-arrow shop-filter-bar-apply"><?php esc_html_e( 'Aplicar', 'air-light' ); ?></button>
        </div>
      </details>

      <?php if ( $has_active_filters ) : ?>
        <a href="<?php echo esc_url( $archive_url ); ?>" class="shop-filter-bar-clear"><?php esc_html_e( 'Limpiar filtros', 'air-light' ); ?></a>
      <?php endif; ?>
    </form>

    <?php if ( woocommerce_product_loop() ) : ?>
      <p class="shop-filter-bar-count">
        <?php
        global $wp_query;
        printf(
          esc_html(
            /* translators: %d: number of products found. */
            _n( '%d producto encontrado', '%d productos encontrados', $wp_query->found_posts, 'air-light' )
          ),
          (int) $wp_query->found_posts
        );
        ?>
      </p>
    <?php endif; ?>
  </div>
</div>

<?php
/**
 * Render one product card by post ID, setting up the required post/product
 * globals so woocommerce/content-product.php has what it expects.
 */
$render_product_card = function ( $product_id ) {
  global $post;

  $post = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
  setup_postdata( $post );
  wc_get_template_part( 'content', 'product' );
};

$product_rows = [];

if ( have_posts() ) {
  $product_rows = array_chunk( wp_list_pluck( $wp_query->posts, 'ID' ), $columns );
}

$first_row       = array_shift( $product_rows );
$remaining_rows  = $product_rows;
?>

<div class="wrap-canut">
  <?php if ( $first_row ) : ?>
    <ul class="shop-product-grid">
      <?php foreach ( $first_row as $product_id ) : ?>
        <li><?php $render_product_card( $product_id ); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else : ?>
    <p class="shop-no-products"><?php esc_html_e( 'No se encontraron productos en esta categoría.', 'air-light' ); ?></p>
  <?php endif; ?>
</div>

<?php if ( $remaining_rows ) : ?>
  <div class="shop-lifestyle-break">
    <img
      src="<?php echo esc_url( get_theme_file_uri( 'assets/images/homepage/lifestyle-living-room.jpg' ) ); ?>"
      alt="<?php esc_attr_e( 'Sala de estar acogedora con un comedero CANUT junto a la mesa de centro', 'air-light' ); ?>"
      loading="lazy"
    >
    <div class="wrap-canut">
      <div class="shop-lifestyle-break-content">
        <h2 class="shop-lifestyle-break-title"><?php esc_html_e( 'Diseñado para su rutina, pensado para tu paz.', 'air-light' ); ?></h2>
        <p class="shop-lifestyle-break-text"><?php esc_html_e( 'Nuestros comederos se integran perfectamente en tu hogar, asegurando que tu compañero reciba la porción exacta, siempre a tiempo.', 'air-light' ); ?></p>
      </div>
    </div>
  </div>
<?php endif; ?>

<div class="wrap-canut">
  <?php if ( $remaining_rows ) : ?>
    <ul class="shop-product-grid">
      <?php foreach ( $remaining_rows as $row ) : ?>
        <?php foreach ( $row as $product_id ) : ?>
          <li><?php $render_product_card( $product_id ); ?></li>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php
  wp_reset_postdata();

  if ( $first_row ) {
    woocommerce_pagination();
  }
  ?>

  <div class="shop-trust-banner">
    <div class="shop-trust-banner-item">
      <span class="shop-trust-banner-icon"><?php require get_theme_file_path( 'assets/svg/icon-shield-check.svg' ); ?></span>
      <div>
        <p class="shop-trust-banner-title"><?php esc_html_e( 'Garantía', 'air-light' ); ?></p>
        <p class="shop-trust-banner-text"><?php esc_html_e( '2 años de cobertura técnica oficial.', 'air-light' ); ?></p>
      </div>
    </div>
    <div class="shop-trust-banner-item">
      <span class="shop-trust-banner-icon"><?php require get_theme_file_path( 'assets/svg/icon-truck.svg' ); ?></span>
      <div>
        <p class="shop-trust-banner-title"><?php esc_html_e( 'Envíos', 'air-light' ); ?></p>
        <p class="shop-trust-banner-text"><?php esc_html_e( 'Entrega express en 24/48 horas.', 'air-light' ); ?></p>
      </div>
    </div>
    <div class="shop-trust-banner-item">
      <span class="shop-trust-banner-icon"><?php require get_theme_file_path( 'assets/svg/icon-chat-circle.svg' ); ?></span>
      <div>
        <p class="shop-trust-banner-title"><?php esc_html_e( 'Soporte', 'air-light' ); ?></p>
        <p class="shop-trust-banner-text"><?php esc_html_e( 'Atención directa vía conserjería digital.', 'air-light' ); ?></p>
      </div>
    </div>
  </div>
</div>

<div class="shop-help-cta">
  <img
    src="<?php echo esc_url( get_theme_file_uri( 'assets/images/homepage/emotional-branding.jpg' ) ); ?>"
    alt=""
    loading="lazy"
  >
  <div class="shop-help-cta-content">
    <h2 class="shop-help-cta-title"><?php esc_html_e( '¿No sabes cuál elegir?', 'air-light' ); ?></h2>
    <p class="shop-help-cta-text"><?php esc_html_e( 'Nuestros expertos están listos para asesorarte de forma personalizada para encontrar el comedero perfecto según las necesidades de tu mascota.', 'air-light' ); ?></p>
    <a href="https://wa.me/" class="button-canut-base button-canut-primary" target="_blank" rel="noopener noreferrer">
      <?php require get_theme_file_path( 'assets/svg/icon-whatsapp.svg' ); ?>
      <?php esc_html_e( 'Consultar por WhatsApp', 'air-light' ); ?>
    </a>
  </div>
</div>

<?php

do_action( 'woocommerce_after_main_content' );

get_footer();
