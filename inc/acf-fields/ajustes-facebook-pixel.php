<?php
/**
 * Field group for the "Ajustes de Facebook Pixel" options page, registered in
 * inc/eventos/facebook-conversions-api.php (register_facebook_pixel_options_page).
 * Single source of truth for the Pixel ID + Conversions API credentials every
 * event file under inc/eventos/ sends through (facebook_pixel_settings()).
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'      => 'group_ajustes_facebook_pixel',
  'title'    => __( 'Ajustes de Facebook Pixel', 'air-light' ),
  'fields'   => [
    [
      'key'           => 'field_facebook_pixel_enabled',
      'label'         => __( 'Activar eventos', 'air-light' ),
      'name'          => 'facebook_pixel_enabled',
      'type'          => 'true_false',
      'instructions'  => __( 'Envía los eventos (ViewContent, AddToCart, etc.) a la Conversions API de Meta. Si está desactivado, ningún evento se envía.', 'air-light' ),
      'default_value' => 0,
      'ui'            => 1,
    ],
    [
      'key'          => 'field_facebook_pixel_id',
      'label'        => __( 'Pixel ID', 'air-light' ),
      'name'         => 'facebook_pixel_id',
      'type'         => 'text',
      'instructions' => __( 'ID del pixel de Facebook (Administrador de eventos > Orígenes de datos > tu pixel).', 'air-light' ),
      'wrapper'      => [ 'width' => '50' ],
    ],
    [
      'key'          => 'field_facebook_capi_access_token',
      'label'        => __( 'Conversions API access token', 'air-light' ),
      'name'         => 'facebook_capi_access_token',
      'type'         => 'password',
      'instructions' => __( 'Token generado en Administrador de eventos > tu pixel > Configuración > Conversions API.', 'air-light' ),
      'wrapper'      => [ 'width' => '50' ],
    ],
    [
      'key'          => 'field_facebook_capi_test_event_code',
      'label'        => __( 'Test event code', 'air-light' ),
      'name'         => 'facebook_capi_test_event_code',
      'type'         => 'text',
      'instructions' => __( 'Opcional. Solo mientras pruebas en Administrador de eventos > Pruebas de eventos. Vacío en producción.', 'air-light' ),
      'wrapper'      => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_facebook_capi_custom_events_tab',
      'label' => __( 'Eventos personalizados', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_facebook_capi_custom_events_message',
      'name'         => '',
      'type'         => 'message',
      'message'      => __( 'Meta no tiene un evento estándar para estos pasos del checkout (información de contacto, envío, inicio de pago) ni para un pago rechazado/fallido, así que se envían como eventos personalizados con el nombre configurado abajo. Aparecen en el Administrador de eventos bajo ese nombre - úsalo para crear Conversiones personalizadas si lo necesitas. Vacío = se usa el nombre por defecto.', 'air-light' ),
    ],
    [
      'key'           => 'field_facebook_capi_event_name_contact_info',
      'label'         => __( 'Nombre: paso "Información de contacto"', 'air-light' ),
      'name'          => 'facebook_capi_event_name_contact_info',
      'type'          => 'text',
      'placeholder'   => 'AddContactInfo',
      'instructions'  => __( 'Se envía cuando el checkout ya tiene un email y teléfono válidos.', 'air-light' ),
      'wrapper'       => [ 'width' => '33' ],
    ],
    [
      'key'           => 'field_facebook_capi_event_name_shipping_info',
      'label'         => __( 'Nombre: paso "Envío"', 'air-light' ),
      'name'          => 'facebook_capi_event_name_shipping_info',
      'type'          => 'text',
      'placeholder'   => 'AddShippingInfo',
      'instructions'  => __( 'Se envía cuando el checkout ya tiene dirección, ciudad y código postal.', 'air-light' ),
      'wrapper'       => [ 'width' => '33' ],
    ],
    [
      'key'           => 'field_facebook_capi_event_name_payment_initiated',
      'label'         => __( 'Nombre: pedido creado / pago iniciado', 'air-light' ),
      'name'          => 'facebook_capi_event_name_payment_initiated',
      'type'          => 'text',
      'placeholder'   => 'InitiatePayment',
      'instructions'  => __( 'Se envía justo al crear el pedido, antes de pasar a la pasarela de pago (distinto de Purchase, que solo se envía al confirmarse el pedido).', 'air-light' ),
      'wrapper'       => [ 'width' => '33' ],
    ],
    [
      'key'           => 'field_facebook_capi_event_name_payment_failed',
      'label'         => __( 'Nombre: pago rechazado/fallido', 'air-light' ),
      'name'          => 'facebook_capi_event_name_payment_failed',
      'type'          => 'text',
      'placeholder'   => 'PaymentFailed',
      'instructions'  => __( 'Se envía cuando un pago queda rechazado, en error o el pedido se cancela sin haberse cobrado.', 'air-light' ),
      'wrapper'       => [ 'width' => '33' ],
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'options_page',
        'operator' => '==',
        'value'    => 'ajustes-facebook-pixel',
      ],
    ],
  ],
] );
