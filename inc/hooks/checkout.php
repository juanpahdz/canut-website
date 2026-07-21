<?php
/**
 * Checkout page customization (CANUT redesign).
 *
 * This site uses the WooCommerce Checkout block (Store API), not the
 * classic [woocommerce_checkout] shortcode - the block keeps its own
 * skeleton loaders/AJAX speed, so the CANUT look is applied on top of it
 * via CSS (views/_checkout-canut.scss) plus the two extension points below,
 * instead of overriding woocommerce/checkout/*.php templates (those aren't
 * used by block-based checkout at all).
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Add "Barrio" (neighborhood) to the checkout block's address fields - not
 * a default WooCommerce field. Registered on 'woocommerce_init' per the
 * Additional Checkout Fields API; saved automatically as order meta
 * (`_wc_billing/canut/neighborhood` / `_wc_shipping/canut/neighborhood`),
 * no template changes needed since it renders itself in the address block.
 *
 * @see https://developer.woocommerce.com/docs/block-development/extensible-blocks/cart-and-checkout-blocks/additional-checkout-fields/
 */
function checkout_register_neighborhood_field() {
  if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
    return;
  }

  woocommerce_register_additional_checkout_field( [
    'id'       => 'canut/neighborhood',
    'label'    => __( 'Barrio', 'air-light' ),
    'location' => 'address',
    'type'     => 'text',
    'required' => true,
  ] );
} // end checkout_register_neighborhood_field
add_action( 'woocommerce_init', __NAMESPACE__ . '\checkout_register_neighborhood_field' );

/**
 * Show the "Contraentrega" (cod) payment card before any other gateway, so
 * it always renders as the first/left card regardless of the order
 * gateways happen to be configured in under WooCommerce > Settings > Payments.
 *
 * @param array $gateways Available payment gateways, keyed by gateway id.
 * @return array
 */
function checkout_gateway_order( $gateways ) {
  if ( isset( $gateways['cod'] ) ) {
    $cod = $gateways['cod'];
    unset( $gateways['cod'] );
    $gateways = [ 'cod' => $cod ] + $gateways;
  }

  return $gateways;
} // end checkout_gateway_order
add_filter( 'woocommerce_available_payment_gateways', __NAMESPACE__ . '\checkout_gateway_order' );

/**
 * Coupon code that identifies the automatic online-payment (Wompi) discount.
 * The coupon itself (percentage, minimum spend, expiry, etc.) is managed by
 * store staff under WooCommerce > Marketing > Cupones - this only pins which
 * coupon that is, so the rate lives in WooCommerce's own config instead of
 * being hardcoded here.
 *
 * @return string
 */
function online_payment_discount_coupon_code() {
  return apply_filters( __NAMESPACE__ . '\online_payment_discount_coupon_code', 'PAGOENLINEA8' );
} // end online_payment_discount_coupon_code

/**
 * Fetch the coupon configured for the automatic online-payment discount, if
 * it exists and is currently valid for the cart (published, not expired,
 * usage limits and cart restrictions satisfied). Used both to calculate the
 * checkout fee below and to preview the discount in the cart drawer
 * (template-parts/cart/drawer-content.php), so both stay in sync with
 * whatever staff configure in wc-admin.
 *
 * @return \WC_Coupon|null
 */
function get_online_payment_discount_coupon() {
  if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
    return null;
  }

  $coupon = new \WC_Coupon( online_payment_discount_coupon_code() );

  if ( ! $coupon->get_id() ) {
    return null;
  }

  $valid = ( new \WC_Discounts( WC()->cart ) )->is_coupon_valid( $coupon );

  return is_wp_error( $valid ) ? null : $coupon;
} // end get_online_payment_discount_coupon

/**
 * Human-readable discount label for a coupon, e.g. "8%" for a percentage
 * coupon or a formatted price for a fixed-amount one.
 *
 * @param \WC_Coupon $coupon Coupon to format.
 * @return string
 */
function format_coupon_discount_label( $coupon ) {
  if ( 'percent' === $coupon->get_discount_type() ) {
    return $coupon->get_amount() . '%';
  }

  return wp_strip_all_tags( wc_price( $coupon->get_amount() ) );
} // end format_coupon_discount_label

/**
 * Automatic discount for paying online (Wompi) instead of contraentrega.
 * The rate comes from the coupon configured in online_payment_discount_coupon_code()
 * above, not a hardcoded percentage. WooCommerce recalculates cart totals
 * (and this fee) whenever the customer changes the selected payment method
 * in the checkout block.
 *
 * @param \WC_Cart $cart Current cart.
 */
function checkout_online_payment_discount( $cart ) {
  if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
    return;
  }

  if ( ! WC()->session || 'wompi' !== WC()->session->get( 'chosen_payment_method' ) ) {
    return;
  }

  $coupon = get_online_payment_discount_coupon();

  if ( ! $coupon ) {
    return;
  }

  $discount = $coupon->get_discount_amount( $cart->get_subtotal() );

  if ( $discount > 0 ) {
    $cart->add_fee(
      sprintf(
        /* translators: %s: coupon discount label, e.g. "8%". */
        __( 'Descuento pago en línea (%s)', 'air-light' ),
        format_coupon_discount_label( $coupon )
      ),
      -$discount
    );
  }
} // end checkout_online_payment_discount
add_action( 'woocommerce_cart_calculate_fees', __NAMESPACE__ . '\checkout_online_payment_discount' );
