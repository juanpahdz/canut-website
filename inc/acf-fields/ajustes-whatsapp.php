<?php
/**
 * Field group for the "Ajustes de WhatsApp" options page, registered in
 * inc/hooks/whatsapp.php (register_whatsapp_options_page). Single source of
 * truth for the Ventas/Soporte WhatsApp numbers used across the theme via
 * get_whatsapp_url()/get_whatsapp_number() (same file).
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'      => 'group_ajustes_whatsapp',
  'title'    => __( 'Ajustes de WhatsApp', 'air-light' ),
  'fields'   => [
    [
      'key'          => 'field_whatsapp_ventas',
      'label'        => __( 'Número de Ventas', 'air-light' ),
      'name'         => 'whatsapp_ventas',
      'type'         => 'text',
      'instructions' => __( 'Solo dígitos con código de país, ej. 573001234567 (sin +, espacios ni guiones). Usado en el header, footer, tienda y botones "Comprar por WhatsApp".', 'air-light' ),
      'wrapper'      => [ 'width' => '50' ],
    ],
    [
      'key'          => 'field_whatsapp_soporte',
      'label'        => __( 'Número de Soporte', 'air-light' ),
      'name'         => 'whatsapp_soporte',
      'type'         => 'text',
      'instructions' => __( 'Solo dígitos con código de país, ej. 573001234567 (sin +, espacios ni guiones). Usado en garantía, centro de ayuda y el CTA de ayuda del producto.', 'air-light' ),
      'wrapper'      => [ 'width' => '50' ],
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'options_page',
        'operator' => '==',
        'value'    => 'ajustes-whatsapp',
      ],
    ],
  ],
] );
