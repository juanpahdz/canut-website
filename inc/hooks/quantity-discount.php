<?php
/**
 * "Lleva X y ahorra Y%" - per-product quantity discount, configured per
 * product via ACF (inc/acf-fields/product-canut.php: qty_discount_enabled,
 * qty_discount_min_qty, qty_discount_percent).
 *
 * WooCommerce coupons can't condition on "same product, quantity >= N" (only
 * cart-wide or per-product amounts), so this is a plain fee - same mechanism
 * as the pre-coupon online-payment discount used to be, kept here because a
 * coupon genuinely can't express this rule, not as a shortcut.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * @param int $product_id
 * @return bool
 */
function qty_discount_is_enabled_for( $product_id ) {
  return (bool) get_field( 'qty_discount_enabled', $product_id );
} // end qty_discount_is_enabled_for

/**
 * @param int $product_id
 * @return int
 */
function qty_discount_min_qty_for( $product_id ) {
  $min_qty = (int) get_field( 'qty_discount_min_qty', $product_id );

  return $min_qty > 1 ? $min_qty : 2;
} // end qty_discount_min_qty_for

/**
 * @param int $product_id
 * @return float
 */
function qty_discount_percent_for( $product_id ) {
  $percent = (float) get_field( 'qty_discount_percent', $product_id );

  return $percent > 0 ? $percent : 10;
} // end qty_discount_percent_for

/**
 * Total discount across every qualifying cart line - used both to add the
 * fee below and to preview "Descuentos" in the cart drawer footer
 * (template-parts/cart/drawer-content.php), so both stay in sync.
 *
 * @param \WC_Cart $cart
 * @return float Positive amount.
 */
function cart_quantity_discount_total( $cart ) {
  $discount = 0;

  foreach ( $cart->get_cart() as $cart_item ) {
    $product_id = $cart_item['product_id'];

    if ( ! qty_discount_is_enabled_for( $product_id ) ) {
      continue;
    }

    if ( $cart_item['quantity'] < qty_discount_min_qty_for( $product_id ) ) {
      continue;
    }

    $discount += $cart_item['line_subtotal'] * ( qty_discount_percent_for( $product_id ) / 100 );
  }

  return $discount;
} // end cart_quantity_discount_total

/**
 * Render the "¡Lleva X y ahorra Y%!" upsell card for a cart line that
 * qualifies for the quantity discount but hasn't reached the minimum yet -
 * shared by the cart drawer (template-parts/cart/drawer-content.php) and the
 * checkout order-summary card (woocommerce/checkout/review-order.php) so
 * both show identical copy instead of two hand-kept-in-sync copies. The
 * button carries both data-canut-cart-qty (drawer's modules/cart-drawer.js)
 * and data-checkout-summary-canut-qty (modules/checkout-canut.js) - each
 * script only ever looks for its own attribute, on buttons inside its own
 * scoped container, so the unused one on either page is inert.
 *
 * @param string      $cart_item_key
 * @param \WC_Product $product
 * @param int         $quantity Current quantity of this line.
 */
function render_qty_discount_upsell( $cart_item_key, $product, $quantity ) {
  if ( ! qty_discount_is_enabled_for( $product->get_id() ) ) {
    return;
  }

  $remaining = max( 0, qty_discount_min_qty_for( $product->get_id() ) - $quantity );

  if ( $remaining <= 0 ) {
    return;
  }
  ?>
  <div class="cart-upsell-canut">
    <div class="cart-upsell-canut-icon">
      <?php require get_theme_file_path( 'assets/svg/icon-gift.svg' ); ?>
    </div>
    <div class="cart-upsell-canut-body">
      <p class="cart-upsell-canut-title">
        <?php echo esc_html( sprintf(
          /* translators: 1: minimum quantity, 2: discount percentage. */
          __( '¡Lleva %1$d y ahorra %2$s%%!', 'air-light' ),
          qty_discount_min_qty_for( $product->get_id() ),
          rtrim( rtrim( number_format( qty_discount_percent_for( $product->get_id() ), 1 ), '0' ), '.' )
        ) ); ?>
      </p>
      <p class="cart-upsell-canut-text">
        <?php echo esc_html( sprintf(
          /* translators: %s: product name. */
          __( 'Agrega otra unidad de %s', 'air-light' ),
          $product->get_name()
        ) ); ?>
      </p>
    </div>
    <button
      type="button"
      class="cart-upsell-canut-button"
      data-canut-cart-qty="increase"
      data-checkout-summary-canut-qty="increase"
      data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
    >
      <?php echo esc_html__( '+ Agregar', 'air-light' ); ?>
    </button>
  </div>
  <?php
} // end render_qty_discount_upsell

/**
 * @param \WC_Cart $cart Current cart.
 */
function cart_quantity_discount_fee( $cart ) {
  if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
    return;
  }

  $discount = cart_quantity_discount_total( $cart );

  if ( $discount > 0 ) {
    $cart->add_fee( __( 'Descuento por cantidad', 'air-light' ), -$discount );
  }
} // end cart_quantity_discount_fee
add_action( 'woocommerce_cart_calculate_fees', __NAMESPACE__ . '\cart_quantity_discount_fee' );
