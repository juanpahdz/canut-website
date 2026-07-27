<?php
/**
 * Admin viewer for the data-processing consent log (inc/hooks/data-consent.php) -
 * its own top-level "Consentimientos" menu item, not nested under WooCommerce,
 * so staff actually see it in the sidebar instead of having to know to look
 * under WooCommerce's own submenu. Read-only - gated behind
 * 'manage_woocommerce', the same capability every other WooCommerce admin
 * screen uses.
 *
 * @package air-light
 */

namespace Air_Light;

add_action( 'admin_menu', __NAMESPACE__ . '\register_data_consent_admin_page' );

function register_data_consent_admin_page() {
  add_menu_page(
    __( 'Consentimientos de datos', 'air-light' ),
    __( 'Consentimientos', 'air-light' ),
    'manage_woocommerce',
    'canut-data-consent',
    __NAMESPACE__ . '\render_data_consent_admin_page',
    'dashicons-privacy'
  );
} // end register_data_consent_admin_page

function render_data_consent_admin_page() {
  if ( ! current_user_can( 'manage_woocommerce' ) ) {
    return;
  }

  require_once get_theme_file_path( 'inc/admin/class-data-consent-list-table.php' );

  $list_table = new Data_Consent_List_Table();
  $list_table->prepare_items();
  ?>
  <div class="wrap">
    <h1><?php esc_html_e( 'Consentimientos de datos', 'air-light' ); ?></h1>
    <p>
      <?php esc_html_e( 'Registro de cada vez que un cliente aceptó la política de tratamiento de datos personales en el paso "Información de contacto" del checkout - incluso si nunca llegó a completar el pedido.', 'air-light' ); ?>
    </p>
    <form method="get">
      <input type="hidden" name="page" value="canut-data-consent" />
      <?php $list_table->search_box( __( 'Buscar por email', 'air-light' ), 'canut-data-consent-search' ); ?>
      <?php $list_table->display(); ?>
    </form>
  </div>

  <dialog id="canut-consent-order-dialog" style="width: min(600px, 90vw); padding: 0; border: none; border-radius: 4px;">
    <div style="padding: 1.5rem;">
      <button type="button" id="canut-consent-order-dialog-close" style="float: right; border: none; background: none; cursor: pointer; font-size: 1.25rem; line-height: 1;" aria-label="<?php echo esc_attr__( 'Cerrar', 'air-light' ); ?>">&times;</button>
      <div id="canut-consent-order-dialog-body"><?php esc_html_e( 'Cargando…', 'air-light' ); ?></div>
    </div>
  </dialog>

  <script>
  ( function() {
    var dialog = document.getElementById( 'canut-consent-order-dialog' );
    var body = document.getElementById( 'canut-consent-order-dialog-body' );
    var closeButton = document.getElementById( 'canut-consent-order-dialog-close' );

    if ( ! dialog || ! body || ! closeButton || 'function' !== typeof dialog.showModal ) {
      return;
    }

    closeButton.addEventListener( 'click', function() {
      dialog.close();
    } );

    document.addEventListener( 'click', function( event ) {
      var trigger = event.target.closest( '.canut-consent-view-order' );
      if ( ! trigger ) {
        return;
      }

      event.preventDefault();
      body.textContent = <?php echo wp_json_encode( __( 'Cargando…', 'air-light' ) ); ?>;
      dialog.showModal();

      var data = new FormData();
      data.append( 'action', 'canut_consent_order_details' );
      data.append( 'nonce', <?php echo wp_json_encode( wp_create_nonce( 'canut_consent_order_details' ) ); ?> );
      data.append( 'order_id', trigger.dataset.orderId );

      fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: data } )
        .then( function( response ) { return response.json(); } )
        .then( function( response ) {
          body.innerHTML = ( response && response.success && response.data && response.data.html )
            ? response.data.html
            : <?php echo wp_json_encode( __( 'No se pudo cargar el pedido.', 'air-light' ) ); ?>;
        } )
        .catch( function() {
          body.textContent = <?php echo wp_json_encode( __( 'No se pudo cargar el pedido.', 'air-light' ) ); ?>;
        } );
    } );
  } )();
  </script>
  <?php
} // end render_data_consent_admin_page

/**
 * AJAX: HTML for the order-details popup above - a quick summary (line
 * items, SKU, quantities, total, status, payment method, shipping address)
 * so staff can see what a consent actually led to without leaving this
 * screen, plus a link to the full order edit screen for anything more.
 */
function ajax_render_consent_order_details() {
  check_ajax_referer( 'canut_consent_order_details', 'nonce' );

  if ( ! current_user_can( 'manage_woocommerce' ) ) {
    wp_send_json_error();
  }

  $order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
  $order    = $order_id ? wc_get_order( $order_id ) : false;

  if ( ! $order instanceof \WC_Order ) {
    wp_send_json_error();
  }

  ob_start();
  ?>
  <h2><?php echo esc_html( sprintf( /* translators: %s: order number. */ __( 'Pedido #%s', 'air-light' ), $order->get_order_number() ) ); ?></h2>
  <table class="widefat striped">
    <thead>
      <tr>
        <th><?php esc_html_e( 'Producto', 'air-light' ); ?></th>
        <th><?php esc_html_e( 'SKU', 'air-light' ); ?></th>
        <th><?php esc_html_e( 'Cant.', 'air-light' ); ?></th>
        <th><?php esc_html_e( 'Subtotal', 'air-light' ); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $order->get_items() as $item ) : ?>
        <?php $product = $item->get_product(); ?>
        <tr>
          <td><?php echo esc_html( $item->get_name() ); ?></td>
          <td><?php echo esc_html( $product ? $product->get_sku() : '' ); ?></td>
          <td><?php echo esc_html( $item->get_quantity() ); ?></td>
          <td><?php echo wp_kses_post( wc_price( $order->get_line_total( $item, false, false ) ) ); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p>
    <strong><?php esc_html_e( 'Estado:', 'air-light' ); ?></strong> <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?><br />
    <strong><?php esc_html_e( 'Total:', 'air-light' ); ?></strong> <?php echo wp_kses_post( wc_price( $order->get_total(), [ 'currency' => $order->get_currency() ] ) ); ?><br />
    <strong><?php esc_html_e( 'Método de pago:', 'air-light' ); ?></strong> <?php echo esc_html( $order->get_payment_method_title() ?: '—' ); ?><br />
    <strong><?php esc_html_e( 'Dirección de envío:', 'air-light' ); ?></strong><br />
    <?php echo wp_kses_post( $order->get_formatted_shipping_address() ?: $order->get_formatted_billing_address() ?: '—' ); ?>
  </p>
  <p>
    <a href="<?php echo esc_url( $order->get_edit_order_url() ); ?>" target="_blank" rel="noopener noreferrer">
      <?php esc_html_e( 'Abrir pedido completo →', 'air-light' ); ?>
    </a>
  </p>
  <?php

  wp_send_json_success( [ 'html' => ob_get_clean() ] );
} // end ajax_render_consent_order_details
add_action( 'wp_ajax_canut_consent_order_details', __NAMESPACE__ . '\ajax_render_consent_order_details' );
