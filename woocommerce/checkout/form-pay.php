<?php
/**
 * Pay for order form (CANUT redesign).
 *
 * Overrides WooCommerce's default checkout/form-pay.php. Reached from
 * template-parts/order/failed.php's "Reintentar pago"/"Cambiar método de
 * pago" links ($order->get_checkout_payment_url()) and from the Wompi
 * gateway's own process_payment() redirect (class-wompi-portal-pagos-gateway.php)
 * whenever a customer needs to (re)choose a payment method for an existing
 * order - core's own version renders a bare <table> + unstyled payment list,
 * which looked completely out of place next to the rest of the CANUT
 * checkout. Reuses existing design-system components instead of introducing
 * new ones: .thank-you-canut-card/-order-table (views/_thank-you-canut.scss,
 * already used for the exact same "read-only order details" table on
 * template-parts/order/success.php), .payment-option-canut-list (this
 * template's own sibling checkout/payment.php), and .button-canut-base
 * (components/_button-canut.scss).
 *
 * Field/hook set intentionally mirrors core's own form-pay.php (same
 * do_action names, same #order_review id, same hidden woocommerce_pay input
 * and woocommerce-pay nonce) so WC_Form_Handler::pay_action() and core's own
 * checkout.js (order-pay branch) keep working unmodified - only the markup
 * around them changed.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 *
 * @var \WC_Order $order
 * @var WC_Payment_Gateway[] $available_gateways
 * @var string $order_button_text
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

$order_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
?>

<div class="wrap-canut">
  <div class="order-pay-canut">

    <div class="order-pay-canut-header">
      <h1 class="order-pay-canut-title"><?php esc_html_e( 'Completa tu pago', 'air-light' ); ?></h1>
      <p class="order-pay-canut-subtitle">
        <?php
        printf(
          /* translators: %s: order number. */
          esc_html__( 'Pedido #%s', 'air-light' ),
          esc_html( $order->get_order_number() )
        );
        ?>
      </p>
    </div>

    <form id="order_review" method="post" class="order-pay-canut-form">

      <div class="thank-you-canut-card">
        <h2 class="thank-you-canut-card-title"><?php esc_html_e( 'Resumen del pedido', 'air-light' ); ?></h2>

        <table class="thank-you-canut-order-table">
          <thead>
            <tr>
              <th><?php esc_html_e( 'Producto', 'air-light' ); ?></th>
              <th class="is-right"><?php esc_html_e( 'Total', 'air-light' ); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $order_items as $item ) : ?>
              <?php
              if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
                continue;
              }
              $product = $item->get_product();
              ?>
              <tr class="thank-you-canut-product-row">
                <td>
                  <div class="thank-you-canut-product">
                    <div class="thank-you-canut-product-thumb">
                      <?php echo $product ? $product->get_image( 'thumbnail' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                    <div class="thank-you-canut-product-info">
                      <p class="thank-you-canut-product-name">
                        <?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) ); ?>
                        <span class="thank-you-canut-product-qty">&times; <?php echo esc_html( $item->get_quantity() ); ?></span>
                      </p>
                      <?php
                      $item_meta = wc_display_item_meta( $item, [ 'echo' => false ] );
                      if ( $item_meta ) :
                      ?>
                        <div class="thank-you-canut-product-meta"><?php echo wp_kses_post( $item_meta ); ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td class="is-right"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <?php foreach ( $order->get_order_item_totals() as $key => $total ) : ?>
              <tr class="<?php echo 'order_total' === $key ? 'is-total' : ''; ?>">
                <th><?php echo esc_html( $total['label'] ); ?></th>
                <td class="is-right"><?php echo wp_kses_post( $total['value'] ); ?></td>
              </tr>
            <?php endforeach; ?>
          </tfoot>
        </table>
      </div>

      <?php do_action( 'woocommerce_pay_order_before_payment' ); ?>

      <div id="payment" class="thank-you-canut-card">
        <h2 class="thank-you-canut-card-title"><?php esc_html_e( 'Método de pago', 'air-light' ); ?></h2>

        <?php if ( $order->needs_payment() ) : ?>

          <?php if ( ! empty( $available_gateways ) ) : ?>
            <ul class="wc_payment_methods payment_methods methods payment-option-canut-list">
              <?php foreach ( $available_gateways as $gateway ) : ?>
                <?php wc_get_template( 'checkout/payment-method.php', [ 'gateway' => $gateway ] ); ?>
              <?php endforeach; ?>
            </ul>
          <?php else : ?>
            <div class="banner-canut is-error">
              <?php require get_theme_file_path( 'assets/svg/icon-warning-circle.svg' ); ?>
              <div class="banner-canut-content">
                <p class="banner-canut-text">
                  <?php
                  echo esc_html(
                    apply_filters(
                      'woocommerce_no_available_payment_methods_message',
                      __( 'Lo sentimos, no hay métodos de pago disponibles para este pedido. Contáctanos si necesitas ayuda.', 'air-light' )
                    )
                  );
                  ?>
                </p>
              </div>
            </div>
          <?php endif; ?>

          <div class="order-pay-canut-submit">
            <input type="hidden" name="woocommerce_pay" value="1" />

            <?php wc_get_template( 'checkout/terms.php' ); ?>

            <?php do_action( 'woocommerce_pay_order_before_submit' ); ?>

            <button type="submit" class="button-canut-base button-canut-primary is-full-width is-no-arrow" id="place_order" value="<?php echo esc_attr( $order_button_text ); ?>" data-value="<?php echo esc_attr( $order_button_text ); ?>">
              <?php require get_theme_file_path( 'assets/svg/icon-lock.svg' ); ?>
              <?php echo esc_html( $order_button_text ); ?>
            </button>

            <?php do_action( 'woocommerce_pay_order_after_submit' ); ?>

            <?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
          </div>

        <?php endif; ?>
      </div>
    </form>

    <?php checkout_render_help_banner(); ?>

  </div>
</div>
