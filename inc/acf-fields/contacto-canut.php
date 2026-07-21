<?php
/**
 * Field group for the "Página Contacto CANUT" ACF block
 * (template-parts/blocks/contacto-canut.php).
 *
 * One block holds every "Contacto" section (hero, formulario, WhatsApp,
 * atención/correo, ubicación) so editing the page means filling in this
 * single block's fields, tab by tab.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

$whatsapp_icon_choices = [
  'phone'    => __( 'Teléfono', 'air-light' ),
  'headset'  => __( 'Auriculares (soporte)', 'air-light' ),
  'whatsapp' => __( 'WhatsApp', 'air-light' ),
  'chat-circle' => __( 'Chat', 'air-light' ),
];

acf_add_local_field_group( [
  'key'    => 'group_contacto_canut',
  'title'  => __( 'Página Contacto CANUT', 'air-light' ),
  'fields' => [

    // Hero
    [
      'key'   => 'field_cc_tab_hero',
      'label' => __( '1. Hero', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_cc_hero_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'hero_title',
      'type'  => 'text',
      'default_value' => 'Contacto',
    ],
    [
      'key'   => 'field_cc_hero_text',
      'label' => __( 'Texto', 'air-light' ),
      'name'  => 'hero_text',
      'type'  => 'textarea',
      'rows'  => 2,
      'default_value' => 'Estamos aquí para acompañarte a ti y a tu mascota. Nuestro equipo de expertos está listo para resolver cualquier duda.',
    ],

    // Formulario
    [
      'key'   => 'field_cc_tab_form',
      'label' => __( '2. Formulario', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_cc_form_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'form_title',
      'type'  => 'text',
      'default_value' => 'Envíanos un mensaje',
    ],
    [
      'key'   => 'field_cc_form_button_label',
      'label' => __( 'Texto del botón', 'air-light' ),
      'name'  => 'form_button_label',
      'type'  => 'text',
      'default_value' => 'Enviar mensaje',
    ],
    [
      'key'          => 'field_cc_form_recipient_email',
      'label'        => __( 'Correo de destino', 'air-light' ),
      'name'         => 'form_recipient_email',
      'type'         => 'email',
      'instructions' => __( 'Los mensajes enviados desde este formulario llegan a este correo.', 'air-light' ),
      'default_value' => 'hola@canut.pet',
    ],
    [
      'key'   => 'field_cc_form_success_message',
      'label' => __( 'Mensaje de éxito', 'air-light' ),
      'name'  => 'form_success_message',
      'type'  => 'text',
      'default_value' => '¡Gracias! Tu mensaje fue enviado, te responderemos pronto.',
    ],
    [
      'key'   => 'field_cc_form_error_message',
      'label' => __( 'Mensaje de error', 'air-light' ),
      'name'  => 'form_error_message',
      'type'  => 'text',
      'default_value' => 'Ocurrió un error al enviar tu mensaje. Intenta de nuevo o escríbenos por WhatsApp.',
    ],

    // WhatsApp
    [
      'key'   => 'field_cc_tab_whatsapp',
      'label' => __( '3. WhatsApp', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_cc_whatsapp_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'whatsapp_title',
      'type'  => 'text',
      'default_value' => 'Atención inmediata por WhatsApp',
    ],
    [
      'key'          => 'field_cc_whatsapp_channels',
      'label'        => __( 'Canales', 'air-light' ),
      'name'         => 'whatsapp_channels',
      'type'         => 'repeater',
      'min'          => 1,
      'max'          => 3,
      'layout'       => 'block',
      'button_label' => __( 'Agregar canal', 'air-light' ),
      'sub_fields'   => [
        [
          'key'     => 'field_cc_channel_icon',
          'label'   => __( 'Ícono', 'air-light' ),
          'name'    => 'icon',
          'type'    => 'select',
          'choices' => $whatsapp_icon_choices,
          'wrapper' => [ 'width' => '20' ],
        ],
        [
          'key'     => 'field_cc_channel_label',
          'label'   => __( 'Texto', 'air-light' ),
          'name'    => 'label',
          'type'    => 'text',
          'wrapper' => [ 'width' => '30' ],
        ],
        [
          'key'           => 'field_cc_channel_type',
          'label'         => __( 'Número', 'air-light' ),
          'name'          => 'channel_type',
          'type'          => 'select',
          'choices'       => [
            'ventas'        => __( 'Ventas (global)', 'air-light' ),
            'soporte'       => __( 'Soporte (global)', 'air-light' ),
            'personalizado' => __( 'Personalizado', 'air-light' ),
          ],
          'default_value' => 'ventas',
          'instructions'  => __( 'Ventas/Soporte usan el número configurado en Ajustes > WhatsApp.', 'air-light' ),
          'wrapper'       => [ 'width' => '25' ],
        ],
        [
          'key'               => 'field_cc_channel_phone',
          'label'             => __( 'Número de WhatsApp', 'air-light' ),
          'name'              => 'phone_number',
          'type'              => 'text',
          'instructions'      => __( 'Formato internacional, ej. 573000000000 (sin +, espacios ni guiones).', 'air-light' ),
          'wrapper'           => [ 'width' => '25' ],
          'conditional_logic' => [
            [
              [
                'field'    => 'field_cc_channel_type',
                'operator' => '==',
                'value'    => 'personalizado',
              ],
            ],
          ],
        ],
      ],
    ],

    // Atención y correo
    [
      'key'   => 'field_cc_tab_info',
      'label' => __( '4. Atención y correo', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_cc_info_heading',
      'label' => __( 'Etiqueta superior', 'air-light' ),
      'name'  => 'info_heading',
      'type'  => 'text',
      'default_value' => 'Atención y correo',
    ],
    [
      'key'   => 'field_cc_contact_email',
      'label' => __( 'Correo visible', 'air-light' ),
      'name'  => 'contact_email',
      'type'  => 'email',
      'default_value' => 'hola@canut.pet',
    ],
    [
      'key'   => 'field_cc_schedule_day_label',
      'label' => __( 'Días de atención', 'air-light' ),
      'name'  => 'schedule_day_label',
      'type'  => 'text',
      'default_value' => 'Lunes a Viernes',
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_cc_schedule_hours',
      'label' => __( 'Horario', 'air-light' ),
      'name'  => 'schedule_hours',
      'type'  => 'text',
      'default_value' => '8:00 am - 6:00 pm',
      'wrapper' => [ 'width' => '50' ],
    ],

    // Ubicación
    [
      'key'   => 'field_cc_tab_location',
      'label' => __( '5. Ubicación', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_cc_address',
      'label'        => __( 'Dirección', 'air-light' ),
      'name'         => 'address',
      'type'         => 'text',
      'default_value' => 'Showroom Medellín, Colombia — Calle 10 # 32-45, El Poblado',
    ],
    [
      'key'          => 'field_cc_map_embed_url',
      'label'        => __( 'URL de mapa incrustado (Google Maps)', 'air-light' ),
      'name'         => 'map_embed_url',
      'type'         => 'url',
      'instructions' => __( 'Google Maps > Compartir > Insertar un mapa > copiar solo la URL de src="".', 'air-light' ),
      'default_value' => 'https://www.google.com/maps?q=Calle+10+%23+32-45,+El+Poblado,+Medell%C3%ADn,+Colombia&output=embed',
    ],
    [
      'key'   => 'field_cc_map_link_url',
      'label' => __( 'Enlace "Ver en Google Maps"', 'air-light' ),
      'name'  => 'map_link_url',
      'type'  => 'url',
      'default_value' => 'https://www.google.com/maps/search/?api=1&query=Calle+10+%23+32-45,+El+Poblado,+Medell%C3%ADn,+Colombia',
    ],

  ],
  'location' => [
    [
      [
        'param'    => 'block',
        'operator' => '==',
        'value'    => 'acf/contacto-canut',
      ],
    ],
  ],
] );
