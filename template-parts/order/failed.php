<?php
/**
 * Thank-you page: failure state (Figma node 119:1322).
 *
 * require'd directly by woocommerce/checkout/thankyou.php (not
 * get_template_part() - see the comment there) in the same scope $order is
 * already set in, so it's simply already in scope here. Rendered for orders
 * in a failed/cancelled/refunded/voided status.
 *
 * @package air-light
 *
 * @var \WC_Order $order
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

$pay_url          = $order->get_checkout_payment_url();
$whatsapp_message = sprintf(
  /* translators: %s: order number. */
  __( 'Hola, tuve un problema al pagar mi pedido #%s. ¿Me pueden ayudar?', 'air-light' ),
  $order->get_order_number()
);
?>

<div class="thank-you-canut thank-you-canut-failure">

  <div class="thank-you-canut-hero">
    <div class="thank-you-canut-icon is-error">
      <?php require get_theme_file_path( 'assets/svg/icon-warning-circle.svg' ); ?>
    </div>
    <h1 class="thank-you-canut-heading">
      <?php esc_html_e( 'Algo no salió como esperábamos', 'air-light' ); ?>
    </h1>
    <p class="thank-you-canut-subtitle">
      <?php esc_html_e( 'Hubo un problema al procesar tu pago o confirmar tu pedido. No te preocupes, no se ha realizado ningún cargo.', 'air-light' ); ?>
    </p>
  </div>

  <div class="thank-you-canut-card thank-you-canut-reasons">
    <p class="thank-you-canut-reasons-title"><?php esc_html_e( 'Posibles motivos', 'air-light' ); ?></p>
    <ul class="thank-you-canut-reasons-list">
      <li>
        <?php require get_theme_file_path( 'assets/svg/icon-hand-coins.svg' ); ?>
        <?php esc_html_e( 'Fondos insuficientes en la cuenta seleccionada.', 'air-light' ); ?>
      </li>
      <li>
        <?php require get_theme_file_path( 'assets/svg/icon-wifi-slash.svg' ); ?>
        <?php esc_html_e( 'Error de conexión temporal con el procesador de pagos.', 'air-light' ); ?>
      </li>
      <li>
        <?php require get_theme_file_path( 'assets/svg/icon-credit-card.svg' ); ?>
        <?php esc_html_e( 'La tarjeta fue rechazada por la entidad bancaria.', 'air-light' ); ?>
      </li>
    </ul>
  </div>

  <div class="thank-you-canut-cta-cluster">
    <a href="<?php echo esc_url( $pay_url ); ?>" class="button-canut-base button-canut-primary is-full-width is-no-arrow">
      <?php require get_theme_file_path( 'assets/svg/icon-arrow-counter-clockwise.svg' ); ?>
      <?php esc_html_e( 'Reintentar pago', 'air-light' ); ?>
    </a>
    <a href="<?php echo esc_url( $pay_url ); ?>" class="button-canut-base button-canut-secondary is-full-width">
      <?php require get_theme_file_path( 'assets/svg/icon-credit-card.svg' ); ?>
      <?php esc_html_e( 'Cambiar método de pago', 'air-light' ); ?>
    </a>
    <a href="<?php echo esc_url( get_whatsapp_url( 'soporte', $whatsapp_message ) ); ?>" class="thank-you-canut-whatsapp-link is-no-arrow" target="_blank" rel="noopener noreferrer">
      <?php require get_theme_file_path( 'assets/svg/icon-whatsapp.svg' ); ?>
      <?php esc_html_e( 'Hablar con un asesor por WhatsApp', 'air-light' ); ?>
    </a>
  </div>

  <div class="thank-you-canut-support">
    <h2 class="thank-you-canut-card-title"><?php esc_html_e( 'Estamos aquí para ayudarte', 'air-light' ); ?></h2>
    <p class="thank-you-canut-support-text">
      <?php esc_html_e( 'Nuestro equipo de concierge está disponible para asegurar que tu experiencia con CANUT sea impecable. Si el problema persiste, podemos procesar tu pedido manualmente.', 'air-light' ); ?>
    </p>
    <p class="thank-you-canut-support-hours">
      <?php require get_theme_file_path( 'assets/svg/icon-clock.svg' ); ?>
      <?php esc_html_e( 'L-V: 09:00 - 18:00', 'air-light' ); ?>
    </p>
  </div>

</div>
