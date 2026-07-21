<?php
/**
 * Cart drawer content: product list + subtotal + checkout CTA.
 *
 * Rendered on page load, and re-rendered wholesale (via cart_drawer_render_content()
 * in inc/hooks/cart-drawer.php) after any add/update/remove AJAX action -
 * both the header cart-count badge and this content refresh from the same
 * `WC()->cart` state, so they can never disagree.
 *
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

$cart = function_exists( 'WC' ) ? WC()->cart : null;

?>

<div class="cart-drawer-canut-body" id="cart-drawer-canut-body">

  <?php if ( ! $cart || $cart->is_empty() ) : ?>

    <p class="cart-drawer-canut-empty">
      <?php echo esc_html__( 'Tu carrito está vacío.', 'air-light' ); ?>
    </p>

  <?php else : ?>

    <div class="cart-drawer-canut-items">
      <?php foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) :
        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

        if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
          continue;
        }

        $max_qty  = $_product->get_max_purchase_quantity();
        $item_data = wc_get_formatted_cart_item_data( $cart_item );

        $qty_discount_remaining = 0;
        if ( qty_discount_is_enabled_for( $_product->get_id() ) ) {
          $qty_discount_remaining = max( 0, qty_discount_min_qty_for( $_product->get_id() ) - $cart_item['quantity'] );
        }
      ?>
        <div class="cart-drawer-canut-item" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
          <a href="<?php echo esc_url( $_product->get_permalink( $cart_item ) ); ?>">
            <?php echo wp_kses_post( $_product->get_image( 'thumbnail' ) ); ?>
          </a>
          <div class="cart-drawer-canut-item-info">
            <p class="cart-drawer-canut-item-name">
              <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
            </p>
            <?php if ( $item_data ) : ?>
              <p class="cart-drawer-canut-item-meta"><?php echo esc_html( wp_strip_all_tags( $item_data ) ); ?></p>
            <?php endif; ?>
            <div class="cart-drawer-canut-item-row">
              <div class="cart-drawer-canut-stepper" role="group" aria-label="<?php esc_attr_e( 'Cantidad', 'air-light' ); ?>">
                <button type="button" data-canut-cart-qty="decrease" <?php disabled( $cart_item['quantity'] <= 1 ); ?> aria-label="<?php esc_attr_e( 'Disminuir cantidad', 'air-light' ); ?>">
                  <?php require get_theme_file_path( 'assets/svg/icon-minus-circle.svg' ); ?>
                </button>
                <output aria-live="polite"><?php echo esc_html( $cart_item['quantity'] ); ?></output>
                <button type="button" data-canut-cart-qty="increase" <?php disabled( $max_qty > 0 && $cart_item['quantity'] >= $max_qty ); ?> aria-label="<?php esc_attr_e( 'Aumentar cantidad', 'air-light' ); ?>">
                  <?php require get_theme_file_path( 'assets/svg/icon-plus-circle.svg' ); ?>
                </button>
              </div>
              <span class="cart-drawer-canut-item-price">
                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
              </span>
            </div>
          </div>
          <button type="button" class="cart-drawer-canut-remove" data-canut-cart-qty="remove" aria-label="<?php echo esc_attr( sprintf(
            /* translators: %s: product name. */
            __( 'Eliminar %s del carrito', 'air-light' ),
            $_product->get_name()
          ) ); ?>">
            <?php require get_theme_file_path( 'assets/svg/icon-trash.svg' ); ?>
          </button>
        </div>

        <?php if ( $qty_discount_remaining > 0 ) : ?>
          <div class="cart-upsell-canut">
            <div class="cart-upsell-canut-icon">
              <?php require get_theme_file_path( 'assets/svg/icon-gift.svg' ); ?>
            </div>
            <div class="cart-upsell-canut-body">
              <p class="cart-upsell-canut-title">
                <?php echo esc_html( sprintf(
                  /* translators: 1: minimum quantity, 2: discount percentage. */
                  __( '¡Lleva %1$d y ahorra %2$s%%!', 'air-light' ),
                  qty_discount_min_qty_for( $_product->get_id() ),
                  rtrim( rtrim( number_format( qty_discount_percent_for( $_product->get_id() ), 1 ), '0' ), '.' )
                ) ); ?>
              </p>
              <p class="cart-upsell-canut-text">
                <?php echo esc_html( sprintf(
                  /* translators: %s: product name. */
                  __( 'Agrega otra unidad de %s al carrito', 'air-light' ),
                  $_product->get_name()
                ) ); ?>
              </p>
            </div>
            <button type="button" class="cart-upsell-canut-button" data-canut-cart-qty="increase" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
              <?php echo esc_html__( '+ Agregar', 'air-light' ); ?>
            </button>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <?php
    // Shown whenever the coupon itself exists and is valid for this cart -
    // not gated on the Wompi gateway being fully configured/available, so
    // the promo is visible (and staff can sanity-check it) even before real
    // API keys are added under WooCommerce > Ajustes > Pagos > Wompi. The
    // discount only actually applies at checkout once Wompi is selectable
    // and chosen there (checkout_online_payment_discount() in checkout.php).
    $online_payment_coupon = get_online_payment_discount_coupon();
    ?>

    <?php if ( $online_payment_coupon ) :
      $subtotal_raw     = $cart->get_subtotal();
      $discount_amount  = $online_payment_coupon->get_discount_amount( $subtotal_raw );
      $discounted_total = max( 0, $subtotal_raw - $discount_amount );
    ?>
      <div class="cart-discount-canut">
        <div class="cart-discount-canut-icon">
          <?php require get_theme_file_path( 'assets/svg/icon-credit-card.svg' ); ?>
        </div>
        <div class="cart-discount-canut-body">
          <p class="cart-discount-canut-title">
            <?php echo esc_html__( 'Paga en línea y ahorra', 'air-light' ); ?>
            <span class="cart-discount-canut-badge"><?php echo esc_html( format_coupon_discount_label( $online_payment_coupon ) ); ?> OFF</span>
          </p>
          <?php if ( $discount_amount > 0 ) : ?>
            <p class="cart-discount-canut-prices">
              <span class="cart-discount-canut-original"><?php echo wp_kses_post( wc_price( $subtotal_raw ) ); ?></span>
              <span class="cart-discount-canut-discounted"><?php echo wp_kses_post( wc_price( $discounted_total ) ); ?></span>
            </p>
          <?php endif; ?>
          <p class="cart-discount-canut-text">
            <?php echo esc_html__( 'El descuento se aplica automáticamente al elegir "Pago en línea" en el checkout. También puedes usar el código:', 'air-light' ); ?>
          </p>
          <div class="cart-discount-canut-code">
            <span class="cart-discount-canut-code-value"><?php echo esc_html( $online_payment_coupon->get_code() ); ?></span>
            <button type="button" class="cart-discount-canut-copy" data-canut-copy="<?php echo esc_attr( $online_payment_coupon->get_code() ); ?>">
              <?php require get_theme_file_path( 'assets/svg/icon-copy.svg' ); ?>
              <span data-canut-copy-label><?php echo esc_html__( 'Copiar', 'air-light' ); ?></span>
            </button>
          </div>
        </div>
      </div>
    <?php endif; ?>

  <?php endif; ?>

</div>

<?php if ( $cart && ! $cart->is_empty() ) :
  $quantity_discount = cart_quantity_discount_total( $cart );
?>
  <div class="cart-drawer-canut-footer">
    <div class="cart-drawer-canut-summary-row">
      <span><?php echo esc_html__( 'Subtotal', 'air-light' ); ?></span>
      <span><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></span>
    </div>
    <div class="cart-drawer-canut-summary-row">
      <span><?php echo esc_html__( 'Descuentos', 'air-light' ); ?></span>
      <span>
        <?php if ( $quantity_discount > 0 ) : ?>
          -<?php echo wp_kses_post( wc_price( $quantity_discount ) ); ?>
        <?php else : ?>
          <?php echo esc_html__( 'Se aplican al pagar', 'air-light' ); ?>
        <?php endif; ?>
      </span>
    </div>
    <div class="cart-drawer-canut-summary-row">
      <span><?php echo esc_html__( 'Envío', 'air-light' ); ?></span>
      <span><?php echo esc_html__( 'Calculado al pagar', 'air-light' ); ?></span>
    </div>
    <div class="cart-drawer-canut-subtotal">
      <span><?php echo esc_html__( 'Total', 'air-light' ); ?></span>
      <span><?php echo wp_kses_post( $cart->get_total() ); ?></span>
    </div>
    <a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="button-canut-base button-canut-checkout is-full-width is-no-arrow">
      <?php require get_theme_file_path( 'assets/svg/icon-lock.svg' ); ?>
      <?php echo esc_html__( 'Pagar de forma segura', 'air-light' ); ?>
    </a>
  </div>
<?php endif; ?>
