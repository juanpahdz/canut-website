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
    [
      'key'           => 'field_whatsapp_bubble_enabled',
      'label'         => __( 'Mensajes animados', 'air-light' ),
      'name'          => 'whatsapp_bubble_enabled',
      'type'          => 'true_false',
      'instructions'  => __( 'Muestra, de a uno por turno, los mensajes de abajo en una burbuja animada sobre el botón flotante de WhatsApp para invitar a los visitantes a escribir.', 'air-light' ),
      'default_value' => 1,
      'ui'            => 1,
    ],
    [
      'key'               => 'field_whatsapp_bubble_messages',
      'label'             => __( 'Mensajes', 'air-light' ),
      'name'              => 'whatsapp_bubble_messages',
      'type'              => 'repeater',
      'min'               => 0,
      'max'               => 6,
      'layout'            => 'table',
      'button_label'      => __( 'Agregar mensaje', 'air-light' ),
      'conditional_logic' => [
        [
          [
            'field'    => 'field_whatsapp_bubble_enabled',
            'operator' => '==',
            'value'    => '1',
          ],
        ],
      ],
      'sub_fields'        => [
        [
          'key'   => 'field_whatsapp_bubble_message',
          'label' => __( 'Mensaje', 'air-light' ),
          'name'  => 'message',
          'type'  => 'text',
        ],
      ],
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
