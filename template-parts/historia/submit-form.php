<?php
/**
 * "Cuenta tu historia" public submission dialog.
 *
 * A <dialog>, shown via showModal() (modules/historia-submit-canut.js) -
 * same convention as the cart drawer (template-parts/cart/drawer.php) and
 * the iframe modal (template-parts/modal/iframe-modal.php): Escape/backdrop-
 * click and focus trapping come from the browser for free. Included once,
 * site-wide, from footer.php, and opened from any `[data-canut-historia-open]`
 * trigger - the product page's "Escribir reseña" button
 * (woocommerce/single-product.php) sets `data-canut-historia-product`/
 * `-product-name` on its trigger to preselect and personalize the dialog for
 * that product; the historias archive's trigger (archive-historia.php) opens
 * it with nothing preselected.
 *
 * Posted to admin-post.php and handled by historia_form_submit()
 * (inc/hooks/historia.php) - same convention as the Soporte/Contacto page
 * forms, since no form plugin is active on this site. Requires a real
 * WooCommerce order id (verified via wc_get_order()) before a story can be
 * created at all, and creates a `draft` Historia post for an editor to
 * review and publish manually.
 *
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only, only decides which banner to show; the actual submit is nonce-verified in historia_form_submit().
$status = isset( $_GET['historia'] ) ? sanitize_key( wp_unslash( $_GET['historia'] ) ) : '';

$products = function_exists( 'wc_get_products' ) ? wc_get_products( [
  'status'  => 'publish',
  'limit'   => -1,
  'orderby' => 'title',
  'order'   => 'ASC',
] ) : [];

$turnstile_settings = historia_turnstile_is_configured() ? historia_turnstile_settings() : null;

?>

<dialog id="historia-submit-canut" class="historia-submit-canut" aria-labelledby="historia-submit-canut-title">

  <div class="historia-submit-canut-header">
    <h2 class="historia-submit-canut-title" id="historia-submit-canut-title"><?php esc_html_e( 'Cuenta tu historia', 'air-light' ); ?></h2>
    <button type="button" class="historia-submit-canut-close" data-canut-historia-close aria-label="<?php esc_attr_e( 'Cerrar', 'air-light' ); ?>">
      <?php require get_theme_file_path( 'assets/svg/icon-x.svg' ); ?>
    </button>
  </div>

  <div class="historia-submit-canut-body">
    <p class="historia-submit-canut-subtitle"><?php esc_html_e( 'Comparte tu experiencia con CANUT. La revisamos y la publicamos pronto.', 'air-light' ); ?></p>

    <?php if ( 'exito' === $status ) : ?>
      <div class="banner-canut is-mint" role="status">
        <?php require get_theme_file_path( 'assets/svg/icon-check.svg' ); ?>
        <p class="banner-canut-text"><?php esc_html_e( '¡Gracias! Tu historia fue enviada y la revisaremos pronto.', 'air-light' ); ?></p>
      </div>
    <?php elseif ( 'error' === $status ) : ?>
      <div class="banner-canut is-error" role="alert">
        <?php require get_theme_file_path( 'assets/svg/icon-info.svg' ); ?>
        <p class="banner-canut-text"><?php esc_html_e( 'Completa tu nombre, calificación, número de pedido e historia antes de enviar. El número de pedido debe corresponder a una compra real.', 'air-light' ); ?></p>
      </div>
    <?php endif; ?>

    <form class="historia-submit-form" id="historia-submit-canut-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
      <input type="hidden" name="action" value="canut_historia_submit">
      <input type="hidden" name="canut_historia_redirect" value="<?php echo esc_url( home_url( add_query_arg( null, null ) ) ); ?>">
      <?php wp_nonce_field( 'canut_historia_form', 'canut_historia_nonce' ); ?>

      <p class="historia-submit-form-hp">
        <label for="canut_historia_hp"><?php esc_html_e( 'Dejar en blanco', 'air-light' ); ?></label>
        <input type="text" id="canut_historia_hp" name="canut_historia_hp" tabindex="-1" autocomplete="off">
      </p>

      <div class="historia-submit-form-field">
        <label class="form-canut-label" for="canut_historia_nombre"><?php esc_html_e( 'Tu nombre o el de tu mascota', 'air-light' ); ?></label>
        <input class="input-canut" type="text" id="canut_historia_nombre" name="canut_historia_nombre" placeholder="<?php echo esc_attr__( 'Ej. Camila & Bruno', 'air-light' ); ?>" autocomplete="name" required>
      </div>

      <div class="historia-submit-form-field">
        <label class="form-canut-label" for="canut_historia_order_id"><?php esc_html_e( 'N.º de pedido', 'air-light' ); ?></label>
        <input class="input-canut" type="text" id="canut_historia_order_id" name="canut_historia_order_id" placeholder="<?php echo esc_attr__( 'Ej. 1029', 'air-light' ); ?>" inputmode="numeric" required>
      </div>

      <div class="historia-submit-form-field">
        <label class="form-canut-label" for="canut_historia_rating"><?php esc_html_e( 'Calificación', 'air-light' ); ?></label>
        <div class="select-canut-wrap">
          <select class="select-canut" id="canut_historia_rating" name="canut_historia_rating" required>
            <option value="5"><?php esc_html_e( '★★★★★ (5 - Excelente)', 'air-light' ); ?></option>
            <option value="4"><?php esc_html_e( '★★★★ (4 - Muy bueno)', 'air-light' ); ?></option>
            <option value="3"><?php esc_html_e( '★★★ (3 - Bueno)', 'air-light' ); ?></option>
            <option value="2"><?php esc_html_e( '★★ (2 - Regular)', 'air-light' ); ?></option>
            <option value="1"><?php esc_html_e( '★ (1 - Malo)', 'air-light' ); ?></option>
          </select>
        </div>
      </div>

      <?php if ( $products ) : ?>
        <div class="historia-submit-form-field">
          <label class="form-canut-label" for="canut_historia_product"><?php esc_html_e( 'Producto (opcional)', 'air-light' ); ?></label>
          <div class="select-canut-wrap">
            <select class="select-canut" id="canut_historia_product" name="canut_historia_product">
              <option value=""><?php esc_html_e( 'Ninguno en particular', 'air-light' ); ?></option>
              <?php foreach ( $products as $product ) : ?>
                <option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->get_name() ); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      <?php endif; ?>

      <div class="historia-submit-form-field">
        <label class="form-canut-label" for="canut_historia_quote"><?php esc_html_e( 'Tu historia', 'air-light' ); ?></label>
        <textarea class="textarea-canut" id="canut_historia_quote" name="canut_historia_quote" rows="4" placeholder="<?php echo esc_attr__( 'Cuéntanos tu experiencia...', 'air-light' ); ?>" required></textarea>
      </div>

      <div class="historia-submit-form-field">
        <label class="form-canut-label" for="canut_historia_foto"><?php esc_html_e( 'Foto (opcional)', 'air-light' ); ?></label>
        <input class="input-canut" type="file" id="canut_historia_foto" name="canut_historia_foto" accept="image/*">
      </div>

      <?php if ( $turnstile_settings ) : ?>
        <div id="historia-submit-canut-turnstile" data-sitekey="<?php echo esc_attr( $turnstile_settings['site_key'] ); ?>"></div>
        <?php
        /**
         * Explicit rendering (render=explicit) - the dialog is closed/hidden
         * at page load, so the implicit auto-render Turnstile normally does
         * on script load can't size its iframe correctly. Instead,
         * historia-submit-canut.js calls window.turnstile.render() itself,
         * right after showModal(), once the widget's container is actually
         * visible. onload=canutTurnstileReady tells that module the API is
         * available - a plain <script> tag here (not wp_enqueue_script)
         * since this template part renders after wp_head() has already
         * fired, so a header-enqueued script would never output.
         */
        ?>
        <script>window.canutTurnstileReady = function () { document.dispatchEvent( new Event( 'canut-turnstile-ready' ) ); };</script>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=canutTurnstileReady&render=explicit" async defer></script>
      <?php endif; ?>

      <button type="submit" class="button-canut-base button-canut-primary is-no-arrow">
        <?php esc_html_e( 'Enviar historia', 'air-light' ); ?>
      </button>
    </form>
  </div>

</dialog>
