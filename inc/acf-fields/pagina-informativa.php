<?php
/**
 * Field group for the "Página informativa" reusable page template
 * (template-pagina-informativa.php).
 *
 * One page = one repeater of numbered sections (title + WYSIWYG), used for
 * Términos de Servicio, Política de Privacidad, Shipping Policy, Warranty,
 * etc. The page title (native WP field) is the H1; this group only adds
 * what the template can't get from core fields.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'    => 'group_pagina_informativa',
  'title'  => __( 'Página informativa', 'air-light' ),
  'fields' => [
    [
      'key'          => 'field_pinf_etiqueta_migas',
      'label'        => __( 'Categoría (migas de pan)', 'air-light' ),
      'name'         => 'etiqueta_migas',
      'type'         => 'text',
      'instructions' => __( 'Ej. "Legal", "Soporte". Se muestra como Inicio > Categoría encima del título.', 'air-light' ),
    ],
    [
      'key'   => 'field_pinf_subtitulo',
      'label' => __( 'Subtítulo', 'air-light' ),
      'name'  => 'subtitulo',
      'type'  => 'textarea',
      'rows'  => 2,
      'instructions' => __( 'Texto breve debajo del título, ej. fecha de última actualización.', 'air-light' ),
    ],
    [
      'key'          => 'field_pinf_secciones',
      'label'        => __( 'Secciones', 'air-light' ),
      'name'         => 'secciones',
      'type'         => 'repeater',
      'min'          => 1,
      'layout'       => 'block',
      'button_label' => __( 'Agregar sección', 'air-light' ),
      'instructions' => __( 'Cada sección aparece numerada en la tabla de contenidos y en el título, en el orden en que las agregues aquí.', 'air-light' ),
      'sub_fields'   => [
        [
          'key'      => 'field_pinf_seccion_titulo',
          'label'    => __( 'Título de la sección', 'air-light' ),
          'name'     => 'titulo',
          'type'     => 'text',
          'required' => 1,
        ],
        [
          'key'          => 'field_pinf_seccion_contenido',
          'label'        => __( 'Contenido', 'air-light' ),
          'name'         => 'contenido',
          'type'         => 'wysiwyg',
          'tabs'         => 'visual',
          'toolbar'      => 'Small',
          'media_upload' => 1,
          'delay'        => 1,
        ],
      ],
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'page_template',
        'operator' => '==',
        'value'    => 'template-pagina-informativa.php',
      ],
    ],
  ],
] );
