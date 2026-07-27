<?php
/**
 * Template for displaying the footer
 *
 * Site footer.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 * @package air-light
 */

namespace Air_Light;

$footer_menu_columns = [
  'footer_company' => __( 'Compañía', 'air-light' ),
  'footer_support' => __( 'Soporte', 'air-light' ),
];

$social_links = [
  [ 'label' => 'Instagram', 'url' => '#', 'icon' => 'instagram' ],
  [ 'label' => 'TikTok', 'url' => '#', 'icon' => 'tiktok' ],
  [ 'label' => 'WhatsApp', 'url' => get_whatsapp_url( 'ventas' ), 'icon' => 'whatsapp' ],
];

$payment_methods = [ 'PayPal', 'Visa', 'Mastercard', __( 'Efectivo', 'air-light' ) ];

$whatsapp_bubble_messages = get_whatsapp_bubble_messages();

?>

</div><!-- #content -->

<?php if ( function_exists( 'is_checkout' ) && is_checkout() ) : ?>

  <footer id="colophon" class="site-footer-checkout">
    <div class="wrap-canut site-footer-checkout-inner">
      <div>
        <p class="site-footer-checkout-brand"><?php bloginfo( 'name' ); ?></p>
        <p class="site-footer-checkout-copyright">
          <?php // translators: %s: current year. ?>
          <?php printf( esc_html__( '© %s CANUT Pet Tech. Excelencia artesanal.', 'air-light' ), esc_html( gmdate( 'Y' ) ) ); ?>
        </p>
      </div>
      <ul class="site-footer-checkout-links">
        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'soporte' ) ) ); ?>" data-canut-iframe-modal><?php echo esc_html__( 'Soporte', 'air-light' ); ?></a></li>
        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'politica-de-devoluciones-y-reembolsos' ) ) ); ?>" data-canut-iframe-modal><?php echo esc_html__( 'Política de devoluciones', 'air-light' ); ?></a></li>
        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'politica-de-envios' ) ) ); ?>" data-canut-iframe-modal><?php echo esc_html__( 'Envíos', 'air-light' ); ?></a></li>
      </ul>
    </div>
  </footer><!-- #colophon -->

  <?php
  // Mobile-only fixed bar (views/_checkout-canut.scss hides it from
  // $breakpoint-mobile up, where the order-summary sidebar - visible again
  // at that width - already shows the full card). Rendered with the cart's
  // real totals so it's correct on first paint; modules/checkout-canut.js
  // then keeps the total in sync with classic checkout's own AJAX-updated
  // order total (.order-total .amount, refreshed by WooCommerce core's own
  // checkout.js on every address/shipping/payment-method change) and
  // forwards a click to the real #place_order button in the sidebar.
  //
  // The arrow below is a plain jump link to that same sidebar card
  // (products, totals, coupon, terms checkbox, real #place_order button),
  // which on mobile now renders in normal page flow at the very end of the
  // form (views/_checkout-canut.scss) instead of being hidden.
  $checkout_cart = function_exists( 'WC' ) ? WC()->cart : null;
  ?>
  <?php if ( $checkout_cart ) : ?>
    <div class="checkout-summary-canut">
      <a href="#order_review" class="checkout-summary-canut-toggle" data-checkout-summary-canut-toggle>
        <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
        <span class="screen-reader-text"><?php echo esc_html__( 'Ver resumen del pedido', 'air-light' ); ?></span>
      </a>
      <div class="checkout-summary-canut-info">
        <span class="checkout-summary-canut-count">
          <?php
          // translators: %d: number of items in the cart.
          echo esc_html( sprintf( _n( '%d producto', '%d productos', $checkout_cart->get_cart_contents_count(), 'air-light' ), $checkout_cart->get_cart_contents_count() ) );
          ?>
        </span>
        <span class="checkout-summary-canut-total" data-checkout-summary-canut-total>
          <?php echo wp_kses_post( wc_price( $checkout_cart->get_total( 'edit' ) ) ); ?>
        </span>
      </div>
      <button
        type="button"
        class="button-canut-base button-canut-checkout checkout-summary-canut-button is-no-arrow"
        data-checkout-summary-canut-button
        disabled
      >
        <?php require get_theme_file_path( 'assets/svg/icon-lock.svg' ); ?>
        <?php echo esc_html__( 'Finalizar compra', 'air-light' ); ?>
      </button>
    </div>
  <?php endif; ?>

<?php else : ?>

<footer id="colophon" class="site-footer site-footer-canut">

  <div class="wrap-canut site-footer-canut-inner">

    <div class="site-footer-canut-columns">

      <div class="site-footer-canut-column site-footer-canut-brand">
        <p class="site-footer-canut-logo">
          <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
            <span class="screen-reader-text"><?php bloginfo( 'name' ); ?></span>
            <?php require get_theme_file_path( THEME_SETTINGS['logo'] ); ?>
          </a>
        </p>
        <p class="site-footer-canut-tagline">
          <?php echo esc_html__( 'Excelencia artesanal en tecnología para mascotas.', 'air-light' ); ?>
        </p>
        <ul class="site-footer-canut-social">
          <?php foreach ( $social_links as $social ) : ?>
            <li>
              <a href="<?php echo esc_url( $social['url'] ); ?>" aria-label="<?php echo esc_attr( $social['label'] ); ?>" target="_blank" rel="noopener noreferrer">
                <?php require get_theme_file_path( 'assets/svg/icon-' . $social['icon'] . '.svg' ); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <?php foreach ( $footer_menu_columns as $location => $title ) :
        if ( ! has_nav_menu( $location ) ) {
          continue;
        }
      ?>
        <div class="site-footer-canut-column">
          <h4 class="site-footer-canut-heading"><?php echo esc_html( $title ); ?></h4>
          <?php wp_nav_menu( [
            'theme_location' => $location,
            'container'      => false,
            'depth'          => 1,
            'items_wrap'     => '<ul class="site-footer-canut-links">%3$s</ul>',
            'walker'         => new Footer_Nav_Walker(),
          ] ); ?>
        </div>
      <?php endforeach; ?>

      <div class="site-footer-canut-column">
        <h4 class="site-footer-canut-heading"><?php echo esc_html__( 'Ubicación', 'air-light' ); ?></h4>
        <p class="site-footer-canut-address">
          <?php echo esc_html__( 'Disponibles en toda Colombia', 'air-light' ); ?>
        </p>
        <p class="site-footer-canut-note">
          <?php echo esc_html__( 'Envíos a todo el país. Garantía certificada.', 'air-light' ); ?>
        </p>
      </div>

    </div>

    <div class="site-footer-canut-bottom">
      <p class="site-footer-canut-copyright">
        <?php // translators: %s: current year. ?>
        <?php printf( esc_html__( '© %s CANUT Pet Tech. Excelencia artesanal.', 'air-light' ), esc_html( gmdate( 'Y' ) ) ); ?>
      </p>
      <ul class="site-footer-canut-payments">
        <?php foreach ( $payment_methods as $payment ) : ?>
          <li><?php echo esc_html( $payment ); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>

</footer><!-- #colophon -->

<?php endif; ?>

<?php get_template_part( 'template-parts/cart/drawer' ); ?>

<?php get_template_part( 'template-parts/modal/iframe-modal' ); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

<?php if ( ! ( function_exists( 'is_checkout' ) && is_checkout() ) ) : ?>
  <div class="whatsapp-float-wrap">
    <?php if ( $whatsapp_bubble_messages ) : ?>
      <div class="whatsapp-float-bubble" data-whatsapp-float-bubble aria-hidden="true">
        <?php foreach ( $whatsapp_bubble_messages as $whatsapp_bubble_message ) : ?>
          <span class="whatsapp-float-bubble-message"><?php echo esc_html( $whatsapp_bubble_message ); ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <a
      href="<?php echo esc_url( get_whatsapp_url( 'ventas' ) ); ?>"
      class="whatsapp-float"
      target="_blank"
      rel="noopener noreferrer"
      aria-label="<?php esc_attr_e( 'Escríbenos por WhatsApp', 'air-light' ); ?>"
    >
      <?php require get_theme_file_path( 'assets/svg/icon-whatsapp.svg' ); ?>
    </a>
  </div>
<?php endif; ?>

<a
  href="#page"
  id="top"
  class="top no-external-link-indicator"
  data-version="<?php echo esc_attr( AIR_LIGHT_VERSION ); ?>"
>
  <span class="screen-reader-text"><?php echo esc_html( get_default_localization( 'Back to top' ) ); ?></span>
  <span aria-hidden="true">&uarr;</span>
</a>

</body>
</html>
