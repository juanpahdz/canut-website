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
