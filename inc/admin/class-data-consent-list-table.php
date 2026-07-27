<?php
/**
 * WP_List_Table for the data-processing consent log (WooCommerce >
 * Consentimientos, inc/admin/consent-log.php), reading straight from the
 * custom table inc/hooks/data-consent.php owns - there's no post type/CRUD
 * API for this, it's a plain append-only audit log.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! class_exists( '\WP_List_Table' ) ) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Data_Consent_List_Table extends \WP_List_Table {
  const PER_PAGE = 20;

  public function __construct() {
    parent::__construct( [
      'singular' => 'consentimiento',
      'plural'   => 'consentimientos',
      'ajax'     => false,
    ] );
  } // end __construct

  /**
   * @return array<string, string>
   */
  public function get_columns() {
    return [
      'email'       => __( 'Email', 'air-light' ),
      'phone'       => __( 'Teléfono', 'air-light' ),
      'accepted_at' => __( 'Aceptado el', 'air-light' ),
      'created_at'  => __( 'Guardado el', 'air-light' ),
      'order_id'    => __( 'Pedido', 'air-light' ),
      'ip_address'  => __( 'IP', 'air-light' ),
    ];
  } // end get_columns

  /**
   * @return array<string, array{0: string, 1: bool}>
   */
  protected function get_sortable_columns() {
    return [
      'email'       => [ 'email', false ],
      'accepted_at' => [ 'accepted_at', true ],
    ];
  } // end get_sortable_columns

  /**
   * @param array  $item
   * @param string $column_name
   * @return string
   */
  protected function column_default( $item, $column_name ) {
    switch ( $column_name ) {
      case 'accepted_at':
      case 'created_at':
        return $item[ $column_name ] ? esc_html( mysql2date( 'j M Y, H:i:s', $item[ $column_name ] ) ) : '—';

      case 'order_id':
        return $this->column_order_id( $item );

      default:
        return isset( $item[ $column_name ] ) && '' !== $item[ $column_name ] ? esc_html( $item[ $column_name ] ) : '—';
    }
  } // end column_default

  /**
   * A "Ver pedido #123" button that opens the order-details popup
   * (render_data_consent_admin_page()'s <dialog>, wired up by its own inline
   * script - see that function) instead of linking straight to the edit
   * screen, so staff can check what the consent actually led to without
   * leaving this list. Falls back to a plain "#123" (no button) if the order
   * was deleted since - never breaks the consent record just because the
   * order it led to is gone.
   *
   * @param array $item
   * @return string
   */
  private function column_order_id( $item ) {
    if ( ! $item['order_id'] ) {
      return '—';
    }

    $order = function_exists( 'wc_get_order' ) ? wc_get_order( $item['order_id'] ) : false;

    if ( ! $order ) {
      return esc_html( '#' . $item['order_id'] );
    }

    return sprintf(
      '<button type="button" class="button-link canut-consent-view-order" data-order-id="%d">%s</button>',
      esc_attr( $item['order_id'] ),
      esc_html( '#' . $order->get_order_number() )
    );
  } // end column_order_id

  public function prepare_items() {
    global $wpdb;

    $table = data_consent_table();

    $search = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen, GET-based search/sort/paging like every core WP_List_Table.

    $where  = '';
    $params = [];

    if ( $search ) {
      $where    = 'WHERE email LIKE %s';
      $params[] = '%' . $wpdb->esc_like( $search ) . '%';
    }

    $orderby = isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], [ 'email', 'accepted_at' ], true ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'accepted_at'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $order   = isset( $_REQUEST['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_REQUEST['order'] ) ) ) ? 'ASC' : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

    $this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];

    $total_items = (int) ( $params
      ? $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} {$where}", $params ) ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
      : $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

    $per_page = self::PER_PAGE;
    $offset   = ( $this->get_pagenum() - 1 ) * $per_page;

    $this->items = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT * FROM {$table} {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        array_merge( $params, [ $per_page, $offset ] )
      ),
      ARRAY_A
    ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

    $this->set_pagination_args( [
      'total_items' => $total_items,
      'per_page'    => $per_page,
      'total_pages' => (int) ceil( $total_items / $per_page ),
    ] );
  } // end prepare_items
}
