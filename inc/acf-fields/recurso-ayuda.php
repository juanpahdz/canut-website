<?php
/**
 * ACF field group for the Recurso de ayuda post type (Centro de ayuda).
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'      => 'group_recurso_ayuda',
  'title'    => __( 'Recurso de ayuda', 'air-light' ),
  'fields'   => [
    [
      'key'          => 'field_recurso_ayuda_keywords',
      'label'        => __( 'Palabras clave adicionales', 'air-light' ),
      'name'         => 'palabras_clave',
      'type'         => 'text',
      'instructions' => __( 'Separadas por comas. Amplían la búsqueda del Centro de ayuda a sinónimos o términos que un cliente podría escribir pero que no aparecen en el título o el contenido (ej. "ansiedad, separación, ladridos").', 'air-light' ),
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'post_type',
        'operator' => '==',
        'value'    => 'recurso_ayuda',
      ],
    ],
  ],
] );
