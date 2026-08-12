<?php
/**
 * Checkout page customization (CANUT redesign).
 *
 * This site uses the classic [woocommerce_checkout] shortcode (theme
 * template overrides under woocommerce/checkout/*.php), not the Checkout
 * BLOCK - the block's markup/CSS belongs to the WooCommerce Blocks plugin,
 * which meant fighting its cascade for every visual detail and hard
 * blockers we couldn't touch at all (the Wompi gateway's blocks integration
 * hardcodes its own label; WooCommerce doesn't expose a gateway's
 * description to the block's payment option at all). The classic templates
 * are theme-owned PHP, so the CANUT look is just... the markup, no reskin
 * layer needed - see woocommerce/checkout/*.php for the templates and
 * views/_checkout-canut.scss for the styling.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Core pairs woocommerce_checkout_payment() (renders checkout/payment.php)
 * with woocommerce_order_review() on the same 'woocommerce_checkout_order_review'
 * action (priorities 10/20 respectively) - meaning by default payment
 * method selection renders inside the order-review sidebar. The CANUT
 * design puts it in the left column as its own numbered step instead (see
 * woocommerce/checkout/form-checkout.php, which calls
 * \woocommerce_checkout_payment() directly there) - un-pair it here so it
 * doesn't also render a second time in the sidebar.
 */
function checkout_unhook_payment_from_order_review() {
  remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
} // end checkout_unhook_payment_from_order_review
add_action( 'wp', __NAMESPACE__ . '\checkout_unhook_payment_from_order_review' );

/**
 * Core hooks the coupon toggle/form onto woocommerce_before_checkout_form,
 * which fires before <form class="checkout"> even opens - the CANUT design
 * instead shows it inside the "Método de pago" step, under the payment
 * cards. Core's coupon widget is itself a <form>, which still can't nest
 * inside the main checkout <form> (see woocommerce/checkout/review-order.php's
 * docblock for the full nested-form story) - rather than fight that, this
 * un-hooks core's own version entirely and checkout_render_coupon_field()
 * below renders a lightweight non-<form> replacement instead, called
 * directly from inside the payment step in
 * woocommerce/checkout/form-checkout.php.
 */
function checkout_unhook_coupon_form() {
  remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
} // end checkout_unhook_coupon_form
add_action( 'wp', __NAMESPACE__ . '\checkout_unhook_coupon_form' );

/**
 * Add "Barrio" (neighborhood) to both the billing and shipping field sets -
 * not a default WooCommerce field. Classic checkout's custom-field recipe:
 * register via the *_fields filters below, then explicitly save it (unlike
 * core fields, arbitrary custom ones aren't auto-persisted).
 *
 * Unlike woocommerce_checkout_fields (nested under 'billing'/'shipping'
 * sub-arrays), woocommerce_billing_fields/woocommerce_shipping_fields each
 * receive a FLAT array already keyed by the field's full name
 * (billing_first_name, billing_email, ...) - the key here has to include
 * that same prefix itself, or WooCommerce ends up registering/rendering a
 * field literally called "neighborhood" instead of "billing_neighborhood"/
 * "shipping_neighborhood", which checkout_save_neighborhood_field() below
 * (and every other bit of code expecting that exact name) would never see.
 *
 * @param array $fields Existing address fields, keyed by full field name.
 * @return array
 */
function checkout_add_neighborhood_field( $fields ) {
  $prefix = 'woocommerce_shipping_fields' === current_filter() ? 'shipping_' : 'billing_';

  $fields[ $prefix . 'neighborhood' ] = [
    'label' => __( 'Barrio', 'air-light' ),
    'required' => true,
    'class' => [ 'form-canut-group' ],
    'placeholder' => __( 'Ej. Chapinero', 'air-light' ),
    'priority' => 65, // Right after city/state, before postcode.
  ];

  return $fields;
} // end checkout_add_neighborhood_field
add_filter( 'woocommerce_billing_fields', __NAMESPACE__ . '\checkout_add_neighborhood_field' );
add_filter( 'woocommerce_shipping_fields', __NAMESPACE__ . '\checkout_add_neighborhood_field' );

/**
 * Persist the Barrio field to order meta on checkout - custom (non-core)
 * checkout fields aren't saved automatically the way core's billing_ and
 * shipping_ fields are.
 *
 * @param int $order_id Order being created.
 */
function checkout_save_neighborhood_field( $order_id ) {
  foreach ( [ 'billing_neighborhood', 'shipping_neighborhood' ] as $key ) {
    if ( ! empty( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
      update_post_meta( $order_id, '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    }
  }
} // end checkout_save_neighborhood_field
add_action( 'woocommerce_checkout_update_order_meta', __NAMESPACE__ . '\checkout_save_neighborhood_field' );

/**
 * Show the Barrio field alongside the rest of the billing address on the
 * admin order edit screen (WooCommerce > Pedidos), since it's a custom
 * field core doesn't know to display there on its own.
 *
 * @param array $fields Admin-screen billing address fields.
 * @return array
 */
function checkout_admin_neighborhood_field( $fields ) {
  $fields['neighborhood'] = [ 'label' => __( 'Barrio', 'air-light' ) ];

  return $fields;
} // end checkout_admin_neighborhood_field
add_filter( 'woocommerce_admin_billing_fields', __NAMESPACE__ . '\checkout_admin_neighborhood_field' );
add_filter( 'woocommerce_admin_shipping_fields', __NAMESPACE__ . '\checkout_admin_neighborhood_field' );

/**
 * Dial codes offered by the checkout phone field's country selector (see
 * checkout_render_phone_field() below) - Colombia listed, and selected by
 * default, first. The store only ships within Colombia (Interrapidísimo,
 * Servientrega, Coordinadora - see checkout_render_shipping_info_box()), so
 * the overwhelming majority of orders are Colombian; the rest cover
 * immediate neighbours/regional customers who occasionally order from
 * outside Colombia.
 *
 * @return array<string, array{label: string, code: string, flag: string}>
 */
function phone_country_codes() {
  return apply_filters( __NAMESPACE__ . '\phone_country_codes', [
    'CO' => [ 'label' => __( 'Colombia', 'air-light' ), 'code' => '57', 'flag' => '🇨🇴' ],
    'MX' => [ 'label' => __( 'México', 'air-light' ), 'code' => '52', 'flag' => '🇲🇽' ],
    'US' => [ 'label' => __( 'Estados Unidos', 'air-light' ), 'code' => '1', 'flag' => '🇺🇸' ],
    'EC' => [ 'label' => __( 'Ecuador', 'air-light' ), 'code' => '593', 'flag' => '🇪🇨' ],
    'PE' => [ 'label' => __( 'Perú', 'air-light' ), 'code' => '51', 'flag' => '🇵🇪' ],
    'PA' => [ 'label' => __( 'Panamá', 'air-light' ), 'code' => '507', 'flag' => '🇵🇦' ],
    'VE' => [ 'label' => __( 'Venezuela', 'air-light' ), 'code' => '58', 'flag' => '🇻🇪' ],
    'CL' => [ 'label' => __( 'Chile', 'air-light' ), 'code' => '56', 'flag' => '🇨🇱' ],
    'AR' => [ 'label' => __( 'Argentina', 'air-light' ), 'code' => '54', 'flag' => '🇦🇷' ],
    'ES' => [ 'label' => __( 'España', 'air-light' ), 'code' => '34', 'flag' => '🇪🇸' ],
  ] );
} // end phone_country_codes

/**
 * Render billing_phone as a country-code select + number input pair (CANUT
 * redesign) instead of a single woocommerce_form_field() call - a compound
 * control core's field renderer can't produce on its own. $field still comes
 * from $checkout->get_checkout_fields( 'billing' ) (woocommerce/checkout/form-checkout.php)
 * so label/required/placeholder stay driven by WooCommerce's own field
 * config rather than being hardcoded here.
 *
 * The dial code is context only: billing_phone itself keeps storing exactly
 * what the customer types, unprefixed - matching what every existing
 * consumer already expects (wc-dropi-integration's order sync passes it
 * through unmodified to the Dropi API; template-parts/order/success.php
 * echoes it as-is). The selected code is saved to its own order meta instead
 * of being merged in - see checkout_save_phone_country_code() below.
 *
 * @param array  $field WooCommerce's billing_phone field config.
 * @param string $phone_value Current billing_phone value (posted value on a validation-error re-render, else '').
 * @param string $code_value Current billing_phone_country_code value.
 */
function checkout_render_phone_field( $field, $phone_value, $code_value ) {
  $code_value = $code_value ?: '57';
  $label = $field['label'] ?? __( 'Teléfono', 'air-light' );
  $required = ! empty( $field['required'] );
  $classes = array_merge( $field['class'] ?? [], [ 'form-canut-group' ] );

  // woocommerce_form_field() adds this itself at render time (not stored back
  // into $checkout->get_checkout_fields()'s own config) for every field it
  // renders - since this bypasses that renderer entirely, it has to be added
  // by hand here too, or neither core's own inline validation (checkout.js'
  // validate_field, matched via `.form-row.validate-required`) nor
  // modules/checkout-steps-canut.js's step validation ever flags an empty
  // required phone number.
  if ( $required ) {
    $classes[] = 'validate-required';
  }
  ?>
  <p class="form-row <?php echo esc_attr( implode( ' ', $classes ) ); ?>" id="billing_phone_field">
    <label for="billing_phone">
      <?php echo esc_html( $label ); ?>
      <?php if ( $required ) : ?>
        <abbr class="required" title="<?php echo esc_attr__( 'required', 'woocommerce' ); ?>">*</abbr>
      <?php endif; ?>
    </label>
    <span class="form-canut-phone-group">
      <span class="select-canut-wrap form-canut-phone-code">
        <label for="billing_phone_country_code" class="screen-reader-text"><?php esc_html_e( 'Código de país', 'air-light' ); ?></label>
        <select name="billing_phone_country_code" id="billing_phone_country_code" class="select-canut">
          <?php foreach ( phone_country_codes() as $country ) : ?>
            <option value="<?php echo esc_attr( $country['code'] ); ?>" <?php selected( $code_value, $country['code'] ); ?>>
              <?php echo esc_html( $country['flag'] . ' +' . $country['code'] ); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </span>
      <input
        type="tel"
        class="input-text input-canut"
        name="billing_phone"
        id="billing_phone"
        placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"
        value="<?php echo esc_attr( $phone_value ); ?>"
        autocomplete="tel-national"
        inputmode="numeric"
        pattern="[0-9]*"
        <?php echo $required ? 'required' : ''; ?>
      />
    </span>
  </p>
  <?php
} // end checkout_render_phone_field

/**
 * Persist the selected phone country/dial code to order meta - not a core
 * WooCommerce field (see checkout_render_phone_field() above), so like
 * Barrio (checkout_save_neighborhood_field()) it isn't saved automatically.
 * Kept as its own meta rather than merged into billing_phone, so the phone
 * number's format/content stays exactly what the customer typed.
 *
 * @param int $order_id Order being created.
 */
function checkout_save_phone_country_code( $order_id ) {
  if ( ! empty( $_POST['billing_phone_country_code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
    update_post_meta( $order_id, '_billing_phone_country_code', sanitize_text_field( wp_unslash( $_POST['billing_phone_country_code'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
  }
} // end checkout_save_phone_country_code
add_action( 'woocommerce_checkout_update_order_meta', __NAMESPACE__ . '\checkout_save_phone_country_code' );

/**
 * Render the "add another phone number" repeater shown under the contact
 * step's primary phone field (CANUT redesign) - an alternate contact number
 * (family member, reception desk, etc.) in case the courier can't reach the
 * primary one. Not a real WooCommerce field (there's only ever one
 * billing_phone), so rows are added purely client-side
 * (modules/checkout-steps-canut.js clones the <template> below) and saved as
 * their own order meta - see checkout_save_additional_phones() below.
 *
 * Each row reuses the same country-code-select + number-input pair as
 * checkout_render_phone_field() itself, defaulting to Colombia (matching
 * phone_country_codes()' own default) since additional contacts are
 * overwhelmingly local too.
 */
function checkout_render_additional_phones() {
  ?>
  <div class="form-canut-phone-repeater" data-phone-repeater>
    <div class="form-canut-phone-repeater-list" data-phone-repeater-list></div>
    <button type="button" class="form-canut-phone-add" data-phone-repeater-add>
      <?php require get_theme_file_path( 'assets/svg/icon-plus-circle.svg' ); ?>
      <?php esc_html_e( 'Agregar otro número', 'air-light' ); ?>
    </button>
  </div>

  <template data-phone-repeater-template>
    <div class="form-canut-phone-additional-row">
      <span class="form-canut-phone-group">
        <span class="select-canut-wrap form-canut-phone-code">
          <label class="screen-reader-text"><?php esc_html_e( 'Código de país', 'air-light' ); ?></label>
          <select class="select-canut" name="billing_phone_additional_code[]">
            <?php foreach ( phone_country_codes() as $country ) : ?>
              <option value="<?php echo esc_attr( $country['code'] ); ?>" <?php selected( '57', $country['code'] ); ?>>
                <?php echo esc_html( $country['flag'] . ' +' . $country['code'] ); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </span>
        <input
          type="tel"
          class="input-text input-canut"
          name="billing_phone_additional_number[]"
          placeholder="<?php echo esc_attr__( 'Número adicional', 'air-light' ); ?>"
          autocomplete="tel-national"
          inputmode="numeric"
          pattern="[0-9]*"
        />
      </span>
      <button type="button" class="form-canut-phone-remove" data-phone-repeater-remove aria-label="<?php echo esc_attr__( 'Eliminar número', 'air-light' ); ?>">
        <?php require get_theme_file_path( 'assets/svg/icon-x.svg' ); ?>
      </button>
    </div>
  </template>
  <?php
} // end checkout_render_additional_phones

/**
 * Persist the additional phone numbers (checkout_render_additional_phones()
 * above) as a single order meta array of {code, number} pairs - like Barrio/
 * the phone country code, these aren't core fields so nothing saves them
 * automatically. Empty rows (added then left blank, or never added at all)
 * are dropped rather than stored.
 *
 * @param int $order_id Order being created.
 */
function checkout_save_additional_phones( $order_id ) {
  $codes = isset( $_POST['billing_phone_additional_code'] ) ? (array) wp_unslash( $_POST['billing_phone_additional_code'] ) : []; // phpcs:ignore WordPress.Security.NonceVerification.Missing
  $numbers = isset( $_POST['billing_phone_additional_number'] ) ? (array) wp_unslash( $_POST['billing_phone_additional_number'] ) : []; // phpcs:ignore WordPress.Security.NonceVerification.Missing

  $additional_phones = [];

  foreach ( $numbers as $index => $number ) {
    $number = sanitize_text_field( $number );

    if ( '' === $number ) {
      continue;
    }

    $additional_phones[] = [
      'code' => isset( $codes[ $index ] ) ? sanitize_text_field( $codes[ $index ] ) : '57',
      'number' => $number,
    ];
  }

  if ( $additional_phones ) {
    update_post_meta( $order_id, '_billing_phone_additional', $additional_phones );
  }
} // end checkout_save_additional_phones
add_action( 'woocommerce_checkout_update_order_meta', __NAMESPACE__ . '\checkout_save_additional_phones' );

/**
 * Show the additional phone numbers (checkout_save_additional_phones()
 * above) under the billing address on the admin order edit screen
 * (WooCommerce > Pedidos) - saved data nobody on staff can see would be
 * useless. Same hook location core uses for its own billing-address extras.
 *
 * @param \WC_Order $order Order being viewed/edited.
 */
function checkout_admin_display_additional_phones( $order ) {
  $additional_phones = $order->get_meta( '_billing_phone_additional' );

  if ( empty( $additional_phones ) || ! is_array( $additional_phones ) ) {
    return;
  }
  ?>
  <p>
    <strong><?php esc_html_e( 'Números adicionales:', 'air-light' ); ?></strong>
    <?php foreach ( $additional_phones as $phone ) : ?>
      <br><?php echo esc_html( '+' . $phone['code'] . ' ' . $phone['number'] ); ?>
    <?php endforeach; ?>
  </p>
  <?php
} // end checkout_admin_display_additional_phones
add_action( 'woocommerce_admin_order_data_after_billing_address', __NAMESPACE__ . '\checkout_admin_display_additional_phones' );

/**
 * Render the data-processing consent checkbox shown at the bottom of the
 * contact step (CANUT redesign), required to continue to the address step -
 * Colombia's Ley 1581 de 2012 (Habeas Data) requires explicit, informed
 * consent before collecting personal data, so this has to be confirmed
 * before the name/email/phone just entered above are used for anything
 * further down the checkout. Not a core WooCommerce field, so - like Barrio/
 * the phone country code - it isn't validated or saved automatically:
 * checkout_validate_data_consent() enforces it server-side and
 * checkout_save_data_consent() below persists when it was given.
 *
 * Reuses the same `.form-row.validate-required` convention every other
 * custom field here relies on, so modules/checkout-steps-canut.js's
 * validateStepFields() (which already special-cases checkbox inputs) picks
 * this up with no JS changes at all.
 *
 * The link target comes from WordPress' own Privacy Policy page setting
 * (Settings > Privacy in wp-admin, `wp_page_for_privacy_policy` option) via
 * core's get_privacy_policy_url() - not a hardcoded page slug - so it stays
 * correct whenever that setting is changed, without a code change here.
 * get_privacy_policy_url() itself already only returns a URL once that page
 * is published (empty string otherwise), which is why the plain-text
 * fallback below still applies until a real page is set.
 */
function checkout_render_data_consent_field() {
  $privacy_policy_url = get_privacy_policy_url();
  ?>
  <p class="form-row form-canut-checkbox validate-required" id="data_processing_consent_field">
    <label for="data_processing_consent">
      <input type="checkbox" name="data_processing_consent" id="data_processing_consent" value="1" />
      <span>
        <?php if ( $privacy_policy_url ) : ?>
          <?php
          echo wp_kses_post(
            sprintf(
              /* translators: %1$s: opening link tag to the privacy/data-processing policy page, %2$s: closing link tag. */
              __( 'Acepto la %1$spolítica de tratamiento de datos personales%2$s.', 'air-light' ),
              '<a href="' . esc_url( $privacy_policy_url ) . '" target="_blank" rel="noopener noreferrer">',
              '</a>'
            )
          );
          ?>
        <?php else : ?>
          <?php esc_html_e( 'Acepto la política de tratamiento de datos personales.', 'air-light' ); ?>
        <?php endif; ?>
        <abbr class="required" title="<?php echo esc_attr__( 'required', 'woocommerce' ); ?>">*</abbr>
      </span>
    </label>
  </p>
  <?php
} // end checkout_render_data_consent_field

/**
 * Reject the order server-side if the data-processing consent checkbox
 * above wasn't checked - the step wizard's own client-side validation
 * (modules/checkout-steps-canut.js) can't be relied on as the only gate,
 * same reasoning as core's own required-field checks.
 */
function checkout_validate_data_consent() {
  if ( empty( $_POST['data_processing_consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
    wc_add_notice( __( 'Debes aceptar la política de tratamiento de datos personales para continuar.', 'air-light' ), 'error' );
  }
} // end checkout_validate_data_consent
add_action( 'woocommerce_checkout_process', __NAMESPACE__ . '\checkout_validate_data_consent' );

/**
 * Persist when data-processing consent was given as order meta - a record
 * that a checkbox was ticked isn't itself proof once the checkout session is
 * gone, so this keeps a timestamp on the order for the same reason a real
 * consent trail normally would.
 *
 * @param int $order_id Order being created.
 */
function checkout_save_data_consent( $order_id ) {
  if ( ! empty( $_POST['data_processing_consent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
    update_post_meta( $order_id, '_data_processing_consent_at', current_time( 'mysql' ) );
  }
} // end checkout_save_data_consent
add_action( 'woocommerce_checkout_update_order_meta', __NAMESPACE__ . '\checkout_save_data_consent' );

/**
 * Records that a wizard step was actually confirmed via its own "Continuar"
 * button, in the WooCommerce session - checkout_validate_steps_complete()
 * below checks this alongside each step's own fields, since a field simply
 * having a value (browser autofill, a previous visit's cached form state)
 * was never proof the customer confirmed anything in *this* checkout
 * attempt. Piggybacks on the generic step-confirmation hook
 * (Air_Light\checkout_step_continued, inc/hooks/checkout-step-actions.php)
 * modules/checkout-steps-canut.js's postStepContinued() already fires for
 * exactly this - no new AJAX endpoint needed.
 *
 * @param int $step Confirmed step number (1-3 - see checkout_step_continue()).
 */
function checkout_track_confirmed_step( $step ) {
  if ( ! WC()->session ) {
    return;
  }

  $confirmed_steps = (array) WC()->session->get( 'canut_confirmed_steps', [] );
  $confirmed_steps[ $step ] = true;
  WC()->session->set( 'canut_confirmed_steps', $confirmed_steps );
} // end checkout_track_confirmed_step
add_action( __NAMESPACE__ . '\checkout_step_continued', __NAMESPACE__ . '\checkout_track_confirmed_step' );

/**
 * Backend counterpart of modules/checkout-steps-canut.js's own submit gate
 * (blockSubmitUntilStepsConfirmed()) - that script blocks the real
 * #place_order submit unless every step wizard section shows as confirmed,
 * but that's a purely client-side "is-confirmed" class with nothing stopping
 * a submission that never runs it at all (dev tools, JS disabled, a direct
 * POST to admin-ajax.php's checkout endpoint).
 *
 * WooCommerce core's own validate_checkout() (class-wc-checkout.php) already
 * rejects a missing/invalid payment method and, once a shipping country is
 * known, a missing shipping method - but "Envío"/"Método de pago" ship
 * pre-populated with a first-available default the moment the page loads
 * (checkout_render_shipping_methods() above, core's own default gateway
 * selection), so those defaults alone already satisfy core's checks even if
 * a customer's request never actually reached those steps at all.
 *
 * Checking each step's own $_POST keys alone still isn't enough either:
 * browser autofill or a previous visit's cached form values can leave a
 * locked, never-opened step's fields already holding valid-looking data, so
 * this also requires checkout_track_confirmed_step()'s own session record
 * that the customer actually clicked that step's "Continuar" in this
 * checkout attempt - the one signal that can't be satisfied by cached/
 * pre-filled data alone. Reports everything as one clear, step-mapped
 * message rather than whatever mix of per-field notices core happens to add.
 */
function checkout_validate_steps_complete() {
  $incomplete_steps = [];
  $confirmed_steps = WC()->session ? (array) WC()->session->get( 'canut_confirmed_steps', [] ) : [];

  // Step 1 - Información de contacto.
  if (
    empty( $confirmed_steps[1] )
    || empty( $_POST['billing_email'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
    || empty( $_POST['billing_phone'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
    || empty( $_POST['data_processing_consent'] ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
  ) {
    $incomplete_steps[] = __( 'Información de contacto', 'air-light' );
  }

  // Step 2 - Envío (only relevant for orders that actually need a shipping address).
  if ( WC()->cart && WC()->cart->needs_shipping_address() ) {
    $step_2_incomplete = empty( $confirmed_steps[2] );

    foreach ( [ 'billing_address_1', 'billing_neighborhood', 'billing_city', 'billing_state' ] as $key ) {
      if ( empty( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $step_2_incomplete = true;
        break;
      }
    }

    if ( $step_2_incomplete ) {
      $incomplete_steps[] = __( 'Envío', 'air-light' );
    }
  }

  // Step 3 - Método de pago.
  if ( WC()->cart && WC()->cart->needs_payment() && ( empty( $confirmed_steps[3] ) || empty( $_POST['payment_method'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $incomplete_steps[] = __( 'Método de pago', 'air-light' );
  }

  // Step 4 - Método de envío. No "Continuar" of its own to confirm (it's the
  // last step before the sidebar's real submit button) - complete once
  // "Método de pago" itself is confirmed, same rule the frontend wizard
  // itself uses (isStepIncomplete(), modules/checkout-steps-canut.js).
  if ( WC()->cart && WC()->cart->needs_shipping() && ( empty( $confirmed_steps[3] ) || empty( $_POST['shipping_method'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $incomplete_steps[] = __( 'Método de envío', 'air-light' );
  }

  if ( $incomplete_steps ) {
    wc_add_notice(
      sprintf(
        /* translators: %s: comma-separated list of incomplete checkout step names. */
        __( 'Completa estos pasos antes de finalizar tu compra: %s.', 'air-light' ),
        implode( ', ', $incomplete_steps )
      ),
      'error'
    );
  }
} // end checkout_validate_steps_complete
add_action( 'woocommerce_checkout_process', __NAMESPACE__ . '\checkout_validate_steps_complete' );

/**
 * Fields that should span the full width of the step's 2-column field grid
 * (woocommerce/checkout/form-checkout.php's .checkout-step-canut-body-grid)
 * instead of sitting side by side with a neighbour.
 */
const FULL_WIDTH_CHECKOUT_FIELDS = [ 'billing_country', 'billing_address_1', 'billing_address_2', 'order_comments' ];

/**
 * Style every checkout field (billing/shipping/order/account) with the
 * design system's .form-canut-group (_dev/assets/src/sass/components/_form-canut.scss)
 * instead of setting label_class/input_class per field - it styles whatever
 * <label>/.woocommerce-input-wrapper markup core renders on its own, and
 * already accounts for wc-dropi-integration swapping #billing_city from an
 * input to a select at runtime. Full-width fields (see
 * FULL_WIDTH_CHECKOUT_FIELDS above) get an extra modifier class instead of
 * being targeted by id in CSS.
 *
 * @param array $fields All checkout fields, grouped by fieldset.
 * @return array
 */
function checkout_add_field_classes( $fields ) {
  foreach ( $fields as $fieldset => $fieldset_fields ) {
    foreach ( $fieldset_fields as $key => $field ) {
      $classes = [ 'form-canut-group' ];

      if ( in_array( $key, FULL_WIDTH_CHECKOUT_FIELDS, true ) ) {
        $classes[] = 'form-canut-group--full';
      }

      $fields[ $fieldset ][ $key ]['class'] = array_merge(
        $field['class'] ?? [],
        $classes
      );
    }
  }

  return $fields;
} // end checkout_add_field_classes
add_filter( 'woocommerce_checkout_fields', __NAMESPACE__ . '\checkout_add_field_classes' );

/**
 * Example-value placeholder text per field - none of WooCommerce's default
 * billing/order fields carry one on their own. Select fields (billing_country,
 * billing_state) are left out since core's field template never renders a
 * placeholder for them. billing_phone isn't rendered via woocommerce_form_field()
 * at all (see checkout_render_phone_field() above) but reads $field['placeholder']
 * from this same filtered config, so it's covered here too rather than in its
 * own template.
 *
 * @param array $fields All checkout fields, grouped by fieldset.
 * @return array
 */
function checkout_add_field_placeholders( $fields ) {
  $placeholders = [
    'billing_first_name' => __( 'Juan', 'air-light' ),
    'billing_last_name' => __( 'Pérez', 'air-light' ),
    'billing_company' => __( 'Nombre de tu empresa', 'air-light' ),
    'billing_email' => __( 'correo@ejemplo.com', 'air-light' ),
    'billing_phone' => __( '300 123 4567', 'air-light' ),
    'billing_address_1' => __( 'Calle 123 # 45-67', 'air-light' ),
    'billing_address_2' => __( 'Apartamento, casa, oficina (opcional)', 'air-light' ),
    'billing_city' => __( 'Bogotá', 'air-light' ),
    'billing_postcode' => __( '110111', 'air-light' ),
    'order_comments' => __( 'Ej. dejar el pedido en portería', 'air-light' ),
  ];

  foreach ( $fields as $fieldset => $fieldset_fields ) {
    foreach ( $placeholders as $key => $placeholder ) {
      if ( isset( $fieldset_fields[ $key ] ) ) {
        $fields[ $fieldset ][ $key ]['placeholder'] = $placeholder;
      }
    }
  }

  return $fields;
} // end checkout_add_field_placeholders
add_filter( 'woocommerce_checkout_fields', __NAMESPACE__ . '\checkout_add_field_placeholders' );

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
 * at checkout (classic checkout's own bundled checkout.js triggers the same
 * update_order_review AJAX call on a payment-method change as it does for
 * an address change).
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

/**
 * Numbered step header (CANUT redesign) - a circular badge + heading,
 * wrapping each section in woocommerce/checkout/form-checkout.php. Plain
 * static markup now that we own the template directly, unlike the block
 * checkout's own step-number feature (a CSS counter() on a ::before,
 * fighting WooCommerce Blocks' own reset of it under certain container
 * widths - not worth reproducing that fragility here).
 *
 * @param int    $number Step number to display.
 * @param string $title  Step heading text.
 */
function checkout_step_header( $number, $title ) {
  ?>
  <div class="checkout-step-canut-header">
    <span class="checkout-step-canut-number"><?php echo esc_html( $number ); ?></span>
    <h2 class="checkout-step-canut-title"><?php echo esc_html( $title ); ?></h2>
  </div>
  <?php
} // end checkout_step_header

/**
 * Render the carrier/delivery-time notice shown under the shipping address
 * fields (CANUT redesign). Static copy, not tied to which shipping method
 * ends up selected - shown the same way whether the cart gets the default
 * rate or the Dropi freight-quoted one. Reuses the generic .banner-canut
 * component (design-system) instead of bespoke markup/CSS.
 */
function checkout_render_shipping_info_box() {
  ?>
  <div class="banner-canut is-mint">
    <?php require get_theme_file_path( 'assets/svg/icon-truck.svg' ); ?>
    <div class="banner-canut-content">
      <p class="banner-canut-text"><?php echo esc_html__( 'Tu pedido será enviado con la transportadora con mejor cobertura.', 'air-light' ); ?></p>
      <p class="banner-canut-text"><?php echo esc_html__( '(Interrapidísimo, Servientrega o Coordinadora). Tiempo: 1-3 días.', 'air-light' ); ?></p>
    </div>
  </div>
  <?php
} // end checkout_render_shipping_info_box

/**
 * Render the "have a coupon?" toggle + code field inside the "Método de
 * pago" step (CANUT redesign) - deliberately NOT core's own
 * woocommerce_checkout_coupon_form()/checkout/form-coupon.php template
 * (a real <form>, unhooked via checkout_unhook_coupon_form() above), since
 * that can't be nested inside <form class="checkout"> without the browser
 * silently dropping its <form> tag (see woocommerce/checkout/review-order.php's
 * docblock for the full nested-form story) - exactly where this step lives.
 * Plain toggle/input/button markup instead, with no <form> of its own;
 * modules/checkout-steps-canut.js wires the toggle and posts the code
 * directly to WooCommerce's own apply_coupon AJAX endpoint (same request
 * core's JS would have made, just triggered without a submit event).
 * Already-applied coupons still render/remove themselves the normal way, via
 * wc_cart_totals_coupon_html() in the order-summary sidebar - this only
 * covers entering a new one.
 */
function checkout_render_coupon_field() {
  if ( ! wc_coupons_enabled() ) {
    return;
  }
  ?>
  <div class="checkout-coupon-canut">
    <button type="button" class="checkout-coupon-canut-toggle" data-coupon-toggle aria-expanded="false">
      <?php esc_html_e( '¿Tienes un cupón? Ingrésalo aquí', 'air-light' ); ?>
    </button>
    <div class="checkout-coupon-canut-panel" data-coupon-panel hidden>
      <label for="checkout_coupon_code_canut" class="screen-reader-text"><?php esc_html_e( 'Código del cupón', 'air-light' ); ?></label>
      <input
        type="text"
        id="checkout_coupon_code_canut"
        class="input-text input-canut"
        placeholder="<?php echo esc_attr__( 'Código del cupón', 'air-light' ); ?>"
        data-coupon-input
      />
      <button type="button" class="button-canut-base button-canut-secondary" data-coupon-apply>
        <?php esc_html_e( 'Aplicar', 'air-light' ); ?>
      </button>
    </div>
  </div>
  <?php
} // end checkout_render_coupon_field

/**
 * Render available shipping methods as full-width selectable cards (CANUT
 * redesign) - checkout step 3. Mirrors wc_cart_totals_shipping_html()'s own
 * package/rate loop (core, includes/wc-cart-functions.php) instead of
 * calling it directly, since that renders each package through
 * woocommerce/cart/cart-shipping.php - a <tr>/<ul> meant for the order
 * summary table, not a standalone step. Reuses the payment step's existing
 * .payment-option-canut card markup (see woocommerce/checkout/payment-method.php)
 * rather than a new component, since it's already exactly this pattern: a
 * radio input wrapped in its own clickable <label> card.
 *
 * No "is Dropi active" branching needed here - whatever rates WooCommerce's
 * own shipping-zone matching resolves for the package is what renders,
 * unmodified. If wc-dropi-integration is active and the cart has a product
 * that needs a freight quote, its "dropi_freight" rates appear (it already
 * removes the free_shipping rate itself in that case - see
 * JPIODFW_Shipping::remove_free_shipping_rate()); otherwise whatever else is
 * assigned to the matching shipping zone (typically "Envío gratis") shows
 * instead. That's the same resolution core's own cart/checkout shipping
 * table already relies on, just rendered as cards here.
 */
function checkout_render_shipping_methods() {
  if ( ! WC()->cart || ! WC()->cart->needs_shipping() ) {
    return;
  }

  $packages = WC()->shipping()->get_packages();

  if ( empty( $packages ) ) {
    return;
  }

  foreach ( $packages as $index => $package ) {
    $rates = $package['rates'];

    if ( empty( $rates ) ) {
      ?>
      <p class="checkout-shipping-methods-canut-empty">
        <?php echo esc_html__( 'Ingresa tu dirección de envío para ver las opciones disponibles.', 'air-light' ); ?>
      </p>
      <?php
      continue;
    }

    $chosen_method = wc_get_chosen_shipping_method_for_package( $index, $package );
    ?>
    <ul class="shipping-methods-canut-list">
      <?php foreach ( $rates as $rate ) : ?>
        <?php $option_id = 'shipping_method_' . $index . '_' . sanitize_title( $rate->id ); ?>
        <li class="shipping_method">
          <label for="<?php echo esc_attr( $option_id ); ?>" class="payment-option-canut">
            <div class="payment-option-canut-top">
              <?php require get_theme_file_path( 'assets/svg/icon-truck.svg' ); ?>
              <input
                type="radio"
                name="shipping_method[<?php echo esc_attr( $index ); ?>]"
                data-index="<?php echo esc_attr( $index ); ?>"
                id="<?php echo esc_attr( $option_id ); ?>"
                value="<?php echo esc_attr( $rate->id ); ?>"
                class="shipping_method payment-option-canut-input"
                <?php checked( $rate->id, $chosen_method ); ?>
              />
            </div>
            <div class="payment-option-canut-body">
              <span class="payment-option-canut-title-row">
                <span class="payment-option-canut-title"><?php echo esc_html( $rate->get_label() ); ?></span>
              </span>
              <span class="payment-option-canut-desc"><?php echo wp_kses_post( checkout_format_shipping_rate_cost( $rate ) ); ?></span>
            </div>
          </label>
          <?php do_action( 'woocommerce_after_shipping_rate', $rate, $index ); ?>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php
  }
} // end checkout_render_shipping_methods

/**
 * Cost portion of a shipping rate card (checkout_render_shipping_methods()
 * above) - "Gratis" for a zero-cost rate (free_shipping, or any other method
 * configured with no cost), the formatted price otherwise. Same tax-inclusive/
 * exclusive branching as core's wc_cart_totals_shipping_method_label(), just
 * split out from the method name since the card shows those in two separate
 * lines (title/desc) instead of one combined string.
 *
 * @param \WC_Shipping_Rate $rate Rate to format.
 * @return string
 */
function checkout_format_shipping_rate_cost( $rate ) {
  if ( 0 >= $rate->cost ) {
    return esc_html__( 'Gratis', 'air-light' );
  }

  $cost = WC()->cart->display_prices_including_tax() ? $rate->cost + $rate->get_shipping_tax() : $rate->cost;

  return wc_price( $cost );
} // end checkout_format_shipping_rate_cost

/**
 * Chosen shipping method's label + cost (e.g. "Envío gratis" or "Dropi -
 * Cotizador de flete: $14.500"), shown as the read-only "Envío" row in the
 * order-summary sidebar (woocommerce/checkout/review-order.php) now that
 * picking a method itself happens in its own step (checkout_render_shipping_methods()
 * above) - this only echoes what's currently selected, not another set of
 * radios. Looks the chosen rate up the same way core's own
 * wc_get_chosen_shipping_method_for_package() + $package['rates'] (keyed by
 * rate id) already do, rather than a nonexistent shortcut method.
 */
function checkout_render_chosen_shipping_method_label() {
  $packages = WC()->shipping()->get_packages();
  $labels = [];

  foreach ( $packages as $index => $package ) {
    $chosen_method = wc_get_chosen_shipping_method_for_package( $index, $package );

    if ( empty( $package['rates'][ $chosen_method ] ) ) {
      continue;
    }

    $labels[] = wc_cart_totals_shipping_method_label( $package['rates'][ $chosen_method ] );
  }

  echo implode( ', ', array_map( 'wp_kses_post', $labels ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} // end checkout_render_chosen_shipping_method_label

/**
 * Refresh the shipping-method cards (checkout_render_shipping_methods()
 * above) over classic checkout's own AJAX cycle. Core's bundled checkout.js
 * posts to the update_order_review AJAX action on any address/city change
 * (delegated on `input[name^="shipping_method"]` too, so picking a different
 * card re-triggers it) and swaps in whatever HTML this filter returns, keyed
 * by jQuery selector - the same fragments mechanism cart badges use (see
 * woocommerce_cart_count_fragment() in inc/hooks/woocommerce.php). Without
 * this the cards would only ever reflect the address at page load: they live
 * in the main column (woocommerce/checkout/form-checkout.php), outside
 * #order_review, the only container core's own AJAX response replaces on
 * its own - and Dropi's freight quote is resolved per destination city, so
 * this is what lets a changed city actually re-quote the visible options.
 *
 * @param array $fragments Existing fragments.
 * @return array
 */
function checkout_shipping_methods_fragment( $fragments ) {
  ob_start();
  checkout_render_shipping_methods();
  $fragments['.checkout-shipping-methods-canut'] = '<div class="checkout-shipping-methods-canut">' . ob_get_clean() . '</div>';

  return $fragments;
} // end checkout_shipping_methods_fragment
add_filter( 'woocommerce_update_order_review_fragments', __NAMESPACE__ . '\checkout_shipping_methods_fragment' );

/**
 * Render the "need help" banner shown at the end of the checkout form
 * (CANUT redesign) - a WhatsApp link, not a WooCommerce field. Reuses the
 * generic .banner-canut component's "is-help" (space-between, link on the
 * right) variant.
 */
function checkout_render_help_banner() {
  ?>
  <div class="banner-canut is-help">
    <div class="banner-canut-content">
      <?php require get_theme_file_path( 'assets/svg/icon-headset.svg' ); ?>
      <span class="banner-canut-text"><?php echo esc_html__( '¿Dudas con tu pedido? Escríbenos', 'air-light' ); ?></span>
    </div>
    <a href="<?php echo esc_url( get_whatsapp_url( 'ventas' ) ); ?>" class="banner-canut-link" target="_blank" rel="noopener noreferrer">
      <?php echo esc_html__( 'Contactar por WhatsApp', 'air-light' ); ?>
    </a>
  </div>
  <?php
} // end checkout_render_help_banner

/**
 * Render the trust badges row shown under the "Finalizar compra" button in
 * the order summary sidebar (CANUT redesign) - static reassurance copy, not
 * tied to any real WooCommerce data.
 */
function checkout_render_trust_badges() {
  $badges = [
    [ 'icon' => 'shield-check', 'label' => __( 'Retracto 5 días', 'air-light' ) ],
    [ 'icon' => 'hand-coins', 'label' => __( 'Contraentrega', 'air-light' ) ],
    [ 'icon' => 'headset', 'label' => __( 'Asesoría Premium', 'air-light' ) ],
    [ 'icon' => 'truck', 'label' => __( 'Envío 1-3 días', 'air-light' ) ],
  ];
  ?>
  <div class="checkout-trust-badges-canut">
    <?php foreach ( $badges as $badge ) : ?>
      <div class="checkout-trust-badges-canut-item">
        <?php require get_theme_file_path( 'assets/svg/icon-' . $badge['icon'] . '.svg' ); ?>
        <span><?php echo esc_html( $badge['label'] ); ?></span>
      </div>
    <?php endforeach; ?>
  </div>
  <?php
} // end checkout_render_trust_badges

/**
 * Icon markup for each payment gateway's card (CANUT redesign), keyed by
 * gateway id ('cod', 'wompi'). Neither gateway exposes a CANUT-styled icon
 * of its own (cod's own $icon is filterable but empty by default; wompi's
 * is its own brand logo) - echoed directly in
 * woocommerce/checkout/payment-method.php, keyed by $gateway->id.
 *
 * @return array<string, string>
 */
function checkout_render_payment_method_icons() {
  $icons = [
    'cod' => 'hand-coins',
    'wompi' => 'credit-card',
  ];

  $icons_html = [];

  foreach ( $icons as $gateway_id => $icon_name ) {
    ob_start();
    require get_theme_file_path( 'assets/svg/icon-' . $icon_name . '.svg' );
    $icons_html[ $gateway_id ] = ob_get_clean();
  }

  return $icons_html;
} // end checkout_render_payment_method_icons

/**
 * Truncate a product's short description to a maximum character count for
 * the order-summary item row (woocommerce/checkout/review-order.php) - word
 * count alone (wp_trim_words(), used for the shop/archive cards) still lets
 * one long "sentence" run past the card's width, so this hard-caps at
 * $max_chars, trimmed back to the last full word so it doesn't cut
 * mid-word, with an ellipsis appended only when actually truncated.
 *
 * @param string $text
 * @param int    $max_chars
 * @return string
 */
function checkout_truncate_description( $text, $max_chars = 80 ) {
  $text = trim( wp_strip_all_tags( $text ) );

  if ( mb_strlen( $text ) <= $max_chars ) {
    return $text;
  }

  $truncated  = mb_substr( $text, 0, $max_chars );
  $last_space = mb_strrpos( $truncated, ' ' );

  if ( false !== $last_space ) {
    $truncated = mb_substr( $truncated, 0, $last_space );
  }

  return rtrim( $truncated, " \t\n\r\0\x0B.,;:" ) . '...';
} // end checkout_truncate_description

/**
 * AJAX: quantity stepper inside the checkout order-summary card
 * (woocommerce/checkout/review-order.php, .cart-drawer-canut-stepper reused
 * from the cart drawer). Mutates WC()->cart the same way
 * cart_drawer_update_qty() does (inc/hooks/cart-drawer.php) but only hands
 * back success/failure - modules/checkout-canut.js re-triggers WooCommerce's
 * own 'update_checkout' event afterwards, which re-renders #order_review
 * through its regular AJAX cycle (now that review-order.php's card carries
 * .woocommerce-checkout-review-order-table, the class core's checkout.js
 * actually looks for to swap fragments - see that template), recalculating
 * shipping/fees/totals - including the quantity discount
 * (woocommerce_cart_calculate_fees, inc/hooks/quantity-discount.php) - in
 * that one already-consistent place instead of a second, parallel re-render.
 * Unlike the drawer, decreasing never removes the item (floored at 1) -
 * there's no undo affordance mid-checkout.
 */
function checkout_update_item_qty() {
  check_ajax_referer( 'canut_checkout_summary', 'nonce' );

  $cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
  $action        = isset( $_POST['cart_action'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_action'] ) ) : '';
  $cart          = WC()->cart;

  if ( ! $cart_item_key || ! $cart || ! isset( $cart->get_cart()[ $cart_item_key ] ) ) {
    wp_send_json_error();
  }

  $cart_item = $cart->get_cart()[ $cart_item_key ];

  switch ( $action ) {
    case 'increase':
      $cart->set_quantity( $cart_item_key, $cart_item['quantity'] + 1, true );
      break;

    case 'decrease':
      $cart->set_quantity( $cart_item_key, max( 1, $cart_item['quantity'] - 1 ), true );
      break;

    default:
      wp_send_json_error();
  }

  wp_send_json_success();
} // end checkout_update_item_qty
add_action( 'wp_ajax_canut_checkout_update_qty', __NAMESPACE__ . '\checkout_update_item_qty' );
add_action( 'wp_ajax_nopriv_canut_checkout_update_qty', __NAMESPACE__ . '\checkout_update_item_qty' );

/**
 * Pass the AJAX URL + nonce modules/checkout-canut.js needs for the
 * order-summary quantity stepper, same pattern as cart_drawer_localize_script()
 * (inc/hooks/cart-drawer.php).
 */
function checkout_summary_localize_script() {
  if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
    return;
  }

  wp_localize_script( 'scripts', 'air_light_checkoutSummary', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'canut_checkout_summary' ),
  ] );
} // end checkout_summary_localize_script
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\checkout_summary_localize_script', 20 );
