<?php
/**
 * Thank you / order-received page (CANUT redesign).
 *
 * Overrides WooCommerce's default checkout/thankyou.php. The block-based
 * checkout still falls back to this classic template for the order-received
 * endpoint - see Checkout::is_checkout_endpoint() in the WooCommerce Blocks
 * plugin, which routes order-pay/order-received through the legacy
 * [woocommerce_checkout] shortcode instead of the block itself - so this is
 * the real template in use despite the checkout page itself being block-based
 * (see inc/hooks/checkout.php).
 *
 * Same success/failure branch core itself uses ($order->has_status('failed')),
 * widened to also cover 'cancelled'/'refunded'/'voided' - the Wompi gateway
 * plugin (class-wompi-portal-pagos-gateway-custom.php) already treats those
 * as failure-like on this page via its own woocommerce_thankyou_order_received_text
 * filter, so the same statuses get the CANUT failure view here instead of the
 * success one.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 *
 * @var WC_Order $order
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

$order_failed = $order && $order->has_status( [ 'failed', 'cancelled', 'refunded', 'voided' ] );
?>

<div class="woocommerce-order wrap-canut">

  <?php if ( $order ) : ?>

    <?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

    <?php
    /**
     * Not using get_template_part() here: its $args param is extracted via
     * load_template(), which first extracts every key of $wp_query->query_vars
     * into local scope - and 'order' (ASC/DESC sort direction) is one of
     * WP_Query's own default query vars, clobbering $order back into a
     * string before our $args extraction even runs. A plain require executes
     * in this same local scope instead, so $order (already set above by
     * wc_get_template()'s own extract()) reaches the template part untouched.
     */
    require $order_failed
      ? get_theme_file_path( 'template-parts/order/failed.php' )
      : get_theme_file_path( 'template-parts/order/success.php' );
    ?>

    <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
    <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

  <?php else : ?>

    <?php wc_get_template( 'checkout/order-received.php', [ 'order' => false ] ); ?>

  <?php endif; ?>

</div>
