<?php
/**
 * ACF field group for the Historia post type.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'      => 'group_historia',
  'title'    => __( 'Historia', 'air-light' ),
  'fields'   => [
    [
      'key'   => 'field_historia_rating',
      'label' => __( 'Calificación (1-5)', 'air-light' ),
      'name'  => 'rating',
      'type'  => 'number',
      'default_value' => 5,
      'min'   => 1,
      'max'   => 5,
    ],
    [
      'key'   => 'field_historia_quote',
      'label' => __( 'Testimonio', 'air-light' ),
      'name'  => 'quote',
      'type'  => 'textarea',
      'rows'  => 3,
    ],
    [
      'key'           => 'field_historia_product',
      'label'         => __( 'Producto', 'air-light' ),
      'name'          => 'product',
      'type'          => 'post_object',
      'instructions'  => __( 'Opcional. Si se elige un producto, esta historia también aparece en la sección "Nuestros Clientes" de esa página de producto.', 'air-light' ),
      'post_type'     => [ 'product' ],
      'return_format' => 'id',
      'ui'            => 1,
      'allow_null'    => 1,
    ],
    [
      'key'          => 'field_historia_order_id',
      'label'        => __( 'N.º de pedido', 'air-light' ),
      'name'         => 'order_id',
      'type'         => 'text',
      'instructions' => __( 'Pedido con el que la persona verificó su compra al enviar esta historia desde el sitio (vacío si el editor la creó a mano).', 'air-light' ),
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'post_type',
        'operator' => '==',
        'value'    => 'historia',
      ],
    ],
  ],
] );
