<?php
/**
 * ACF field group for the Categoria de ayuda taxonomy (Centro de ayuda).
 *
 * Icon shown next to the category heading + the "¿Aún más dudas...?" /
 * WhatsApp CTA band rendered at the end of each category section
 * (template-parts/centro-de-ayuda/category-section.php).
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

/**
 * Icon choices for the category heading. Slugs match assets/svg/icon-*.svg
 * (same "select choice slug === SVG filename suffix" convention as
 * inc/acf-fields/product-canut.php's $box_content_icon_choices).
 */
$categoria_ayuda_icon_choices = [
  'package'      => __( 'Producto', 'air-light' ),
  'truck'        => __( 'Envíos', 'air-light' ),
  'credit-card'  => __( 'Pagos', 'air-light' ),
  'shield-check' => __( 'Garantía', 'air-light' ),
  'paw-print'    => __( 'Mascota', 'air-light' ),
  'wrench'       => __( 'Soporte técnico', 'air-light' ),
  'hand-coins'   => __( 'Financiación', 'air-light' ),
  'gift'         => __( 'Promociones', 'air-light' ),
  'chat-circle'  => __( 'Genérico', 'air-light' ),
];

acf_add_local_field_group( [
  'key'      => 'group_categoria_ayuda',
  'title'    => __( 'Categoría de ayuda', 'air-light' ),
  'fields'   => [
    [
      'key'           => 'field_categoria_ayuda_icon',
      'label'         => __( 'Icono', 'air-light' ),
      'name'          => 'icono',
      'type'          => 'select',
      'choices'       => $categoria_ayuda_icon_choices,
      'default_value' => 'chat-circle',
      'allow_null'    => false,
      'ui'            => 1,
    ],
    [
      'key'          => 'field_categoria_ayuda_order',
      'label'        => __( 'Orden', 'air-light' ),
      'name'         => 'orden',
      'type'         => 'number',
      'instructions' => __( 'Orden de aparición en el Centro de ayuda (menor primero). Déjalo vacío para ordenar alfabéticamente al final.', 'air-light' ),
    ],
    [
      'key'   => 'field_categoria_ayuda_cta_tab',
      'label' => __( 'Banda de contacto (WhatsApp)', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_categoria_ayuda_cta_title',
      'label'        => __( 'Título', 'air-light' ),
      'name'         => 'cta_titulo',
      'type'         => 'text',
      'instructions' => __( 'Ej. "¿Aún más dudas sobre el producto?". Si se deja vacío, se usa un texto genérico.', 'air-light' ),
    ],
    [
      'key'   => 'field_categoria_ayuda_cta_description',
      'label' => __( 'Descripción', 'air-light' ),
      'name'  => 'cta_descripcion',
      'type'  => 'text',
    ],
    [
      'key'          => 'field_categoria_ayuda_cta_whatsapp_message',
      'label'        => __( 'Mensaje predefinido de WhatsApp', 'air-light' ),
      'name'         => 'cta_whatsapp_mensaje',
      'type'         => 'text',
      'instructions' => __( 'Opcional. Se añade como texto precargado en el enlace de WhatsApp (wa.me).', 'air-light' ),
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'taxonomy',
        'operator' => '==',
        'value'    => 'categoria_ayuda',
      ],
    ],
  ],
] );
