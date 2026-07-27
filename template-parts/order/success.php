<?php
/**
 * Thank-you page: success state (Figma node 119:1394).
 *
 * require'd directly by woocommerce/checkout/thankyou.php (not
 * get_template_part() - see the comment there) in the same scope $order is
 * already set in, so it's simply already in scope here.
 *
 * @package air-light
 *
 * @var \WC_Order $order
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

$order_items    = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
// Whether the order contains any shippable item at all - not to be confused
// with wc_ship_to_billing_address_only(), which only governs whether a
// *separate* shipping address column is shown (order/order-details-customer.php);
// a "ship to billing only" store still ships physical orders to that address.
$needs_shipping = $order->needs_shipping_address();
$shop_url       = get_permalink( wc_get_page_id( 'shop' ) );
$support_page   = get_page_by_path( 'soporte' );
?>

<div class="thank-you-canut thank-you-canut-success">

  <div class="thank-you-canut-hero">
    <div class="thank-you-canut-icon is-success">
      <?php require get_theme_file_path( 'assets/svg/icon-check-circle.svg' ); ?>
    </div>
    <h1 class="thank-you-canut-heading">
      <?php esc_html_e( '¡Gracias! Tu pedido ha sido recibido.', 'air-light' ); ?>
    </h1>
    <p class="thank-you-canut-subtitle">
      <?php esc_html_e( 'Estamos procesando tu solicitud. Te enviaremos un correo electrónico de confirmación con los detalles del envío pronto.', 'air-light' ); ?>
    </p>
  </div>

  <div class="thank-you-canut-card thank-you-canut-meta-grid">
    <div class="thank-you-canut-meta-item">
      <span class="thank-you-canut-meta-label"><?php esc_html_e( 'Número de pedido', 'air-light' ); ?></span>
      <span class="thank-you-canut-meta-value"><?php echo esc_html( $order->get_order_number() ); ?></span>
    </div>
    <div class="thank-you-canut-meta-item">
      <span class="thank-you-canut-meta-label"><?php esc_html_e( 'Fecha', 'air-light' ); ?></span>
      <span class="thank-you-canut-meta-value"><?php echo esc_html( date_i18n( 'j \d\e F \d\e Y', $order->get_date_created()->getTimestamp() ) ); ?></span>
    </div>
    <?php if ( $order->get_billing_email() ) : ?>
      <div class="thank-you-canut-meta-item">
        <span class="thank-you-canut-meta-label"><?php esc_html_e( 'Email', 'air-light' ); ?></span>
        <span class="thank-you-canut-meta-value is-truncate"><?php echo esc_html( $order->get_billing_email() ); ?></span>
      </div>
    <?php endif; ?>
    <div class="thank-you-canut-meta-item">
      <span class="thank-you-canut-meta-label"><?php esc_html_e( 'Total', 'air-light' ); ?></span>
      <span class="thank-you-canut-meta-value is-accent"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
    </div>
  </div>

  <?php if ( 'cod' === $order->get_payment_method() ) : ?>
    <div class="banner-canut is-mint thank-you-canut-payment-notice">
      <?php require get_theme_file_path( 'assets/svg/icon-hand-coins.svg' ); ?>
      <div>
        <p class="banner-canut-title">
          <?php
          printf(
            /* translators: %s: payment method title, e.g. "Contra reembolso". */
            esc_html__( 'Método de pago: %s', 'air-light' ),
            esc_html( $order->get_payment_method_title() )
          );
          ?>
        </p>
        <p class="banner-canut-text"><?php esc_html_e( 'Paga en efectivo en el momento de la entrega. Por favor, asegúrate de tener el monto exacto disponible para facilitar el proceso.', 'air-light' ); ?></p>
      </div>
    </div>
  <?php elseif ( $order->get_payment_method_title() ) : ?>
    <div class="banner-canut is-mint thank-you-canut-payment-notice">
      <?php require get_theme_file_path( 'assets/svg/icon-check-circle.svg' ); ?>
      <div>
        <p class="banner-canut-title">
          <?php
          printf(
            /* translators: %s: payment method title, e.g. "Wompi". */
            esc_html__( 'Pago confirmado vía %s', 'air-light' ),
            esc_html( $order->get_payment_method_title() )
          );
          ?>
        </p>
        <p class="banner-canut-text"><?php esc_html_e( 'Tu pago fue procesado exitosamente.', 'air-light' ); ?></p>
      </div>
    </div>
  <?php endif; ?>

  <div class="thank-you-canut-card">
    <h2 class="thank-you-canut-card-title"><?php esc_html_e( 'Detalles del pedido', 'air-light' ); ?></h2>

    <table class="thank-you-canut-order-table">
      <thead>
        <tr>
          <th><?php esc_html_e( 'Producto', 'air-light' ); ?></th>
          <th class="is-right"><?php esc_html_e( 'Total', 'air-light' ); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $order_items as $item_id => $item ) :
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
                    <?php echo esc_html( $item->get_name() ); ?>
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
      <?php
      /**
       * $show_payment_method = false: the payment method is already shown in
       * the banner above, no need to repeat it as a table row here. Without
       * it, get_order_item_totals() would append a trailing 'payment_method'
       * row after the real grand total, which is 'order_total' - not simply
       * the last array entry.
       */
      $order_totals = $order->get_order_item_totals( '', false );
      ?>
      <tfoot>
        <?php foreach ( $order_totals as $key => $total ) : ?>
          <tr class="<?php echo 'order_total' === $key ? 'is-total' : ''; ?>">
            <th><?php echo esc_html( $total['label'] ); ?></th>
            <td class="is-right"><?php echo wp_kses_post( $total['value'] ); ?></td>
          </tr>
        <?php endforeach; ?>
      </tfoot>
    </table>
  </div>

  <div class="thank-you-canut-addresses">
    <div class="thank-you-canut-card">
      <h3 class="thank-you-canut-card-title is-small"><?php esc_html_e( 'Dirección de facturación', 'air-light' ); ?></h3>
      <address class="thank-you-canut-address">
        <?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'N/A', 'air-light' ) ) ); ?>
        <?php if ( $order->get_billing_phone() ) : ?>
          <p class="thank-you-canut-address-phone">
            <?php require get_theme_file_path( 'assets/svg/icon-phone.svg' ); ?>
            <?php echo esc_html( $order->get_billing_phone() ); ?>
          </p>
        <?php endif; ?>
      </address>
    </div>

    <?php if ( $needs_shipping ) : ?>
      <div class="thank-you-canut-delivery">
        <?php require get_theme_file_path( 'assets/svg/icon-truck.svg' ); ?>
        <p class="thank-you-canut-delivery-title"><?php esc_html_e( 'En camino a ti', 'air-light' ); ?></p>
        <p class="thank-you-canut-delivery-text">
          <?php echo esc_html( trim( $order->get_shipping_city() . ', ' . $order->get_shipping_state(), ', ' ) ); ?>
        </p>
      </div>
    <?php endif; ?>
  </div>

  <div class="thank-you-canut-actions is-no-print">
    <a href="<?php echo esc_url( $shop_url ); ?>" class="button-canut-base button-canut-primary">
      <?php esc_html_e( 'Volver a la tienda', 'air-light' ); ?>
    </a>
    <?php if ( $support_page ) : ?>
      <a href="<?php echo esc_url( get_permalink( $support_page ) ); ?>" class="button-canut-base button-canut-secondary">
        <?php require get_theme_file_path( 'assets/svg/icon-headset.svg' ); ?>
        <?php esc_html_e( 'Contactar soporte', 'air-light' ); ?>
      </a>
    <?php endif; ?>
    <button type="button" class="button-canut-base button-canut-secondary is-no-arrow" data-thank-you-download-pdf>
      <?php require get_theme_file_path( 'assets/svg/icon-download.svg' ); ?>
      <?php esc_html_e( 'Descargar PDF', 'air-light' ); ?>
    </button>
    <button
      type="button"
      class="button-canut-base button-canut-secondary is-no-arrow"
      data-thank-you-share
      data-share-title="<?php echo esc_attr( sprintf( /* translators: %s: order number. */ __( 'Pedido %s - CANUT', 'air-light' ), $order->get_order_number() ) ); ?>"
      data-share-text="<?php esc_attr_e( 'Este es mi pedido en CANUT.', 'air-light' ); ?>"
      data-share-copied-label="<?php esc_attr_e( '¡Enlace copiado!', 'air-light' ); ?>"
    >
      <?php require get_theme_file_path( 'assets/svg/icon-share.svg' ); ?>
      <span data-thank-you-share-label><?php esc_html_e( 'Compartir', 'air-light' ); ?></span>
    </button>
  </div>

</div>
