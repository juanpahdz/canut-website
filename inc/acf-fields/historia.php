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
      'key'           => 'field_historia_is_featured',
      'label'         => __( 'Aparece en portada', 'air-light' ),
      'name'          => 'is_featured',
      'type'          => 'true_false',
      'instructions'  => __( 'Marca esta historia como favorita para mostrarla en el carrusel de la página de inicio.', 'air-light' ),
      'default_value' => 0,
      'ui'            => 1,
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
