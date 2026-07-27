<?php
/**
 * Single payment method card (CANUT redesign).
 *
 * Overrides WooCommerce's default checkout/payment-method.php. Core only
 * shows a gateway's description inside a conditionally-hidden .payment_box
 * (meant for gateway-specific FIELDS - card number, etc. - shown only once
 * that gateway is selected) - the CANUT cards show icon + title +
 * description always, matching the .payment-option-canut component already
 * built for this in the design system (_dev/assets/src/sass/components/_payment-option-canut.scss).
 * $gateway->payment_fields() (any actual gateway-specific fields) still
 * renders inside the conditional box beneath, in case a gateway needs it -
 * neither cod nor wompi currently do.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 *
 * @var WC_Payment_Gateway $gateway
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

$icons = checkout_render_payment_method_icons();
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
  <label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>" class="payment-option-canut">
    <div class="payment-option-canut-top">
      <?php echo $icons[ $gateway->id ] ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
      <input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="payment-option-canut-input" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />
    </div>
    <div class="payment-option-canut-body">
      <span class="payment-option-canut-title-row">
        <span class="payment-option-canut-title"><?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></span>
      </span>
      <?php if ( $gateway->get_description() ) : ?>
        <span class="payment-option-canut-desc"><?php echo wp_kses_post( $gateway->get_description() ); ?></span>
      <?php endif; ?>
    </div>
  </label>
  <?php if ( $gateway->has_fields() ) : ?>
    <div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" <?php if ( ! $gateway->chosen ) : ?>style="display:none;"<?php endif; ?>>
      <?php $gateway->payment_fields(); ?>
    </div>
  <?php endif; ?>
</li>
