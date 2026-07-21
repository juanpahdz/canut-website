<?php
/**
 * ACF field group for the Pregunta de soporte post type.
 *
 * Holds the submitter's contact details captured by the front-end form
 * (template-parts/soporte/question-form.php) so the reviewing editor can
 * follow up directly - these are never shown publicly, and are separate
 * from the post's own title (pregunta) and editor content (respuesta).
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'      => 'group_pregunta_soporte',
  'title'    => __( 'Datos de contacto', 'air-light' ),
  'fields'   => [
    [
      'key'      => 'field_pregunta_soporte_nombre',
      'label'    => __( 'Nombre completo', 'air-light' ),
      'name'     => 'nombre_remitente',
      'type'     => 'text',
      'required' => 1,
    ],
    [
      'key'      => 'field_pregunta_soporte_email',
      'label'    => __( 'Email', 'air-light' ),
      'name'     => 'email_remitente',
      'type'     => 'email',
      'required' => 1,
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'post_type',
        'operator' => '==',
        'value'    => 'pregunta_soporte',
      ],
    ],
  ],
] );
