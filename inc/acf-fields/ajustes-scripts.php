<?php
/**
 * Field group for the "Ajustes de Scripts" options page, registered in
 * inc/hooks/custom-scripts.php (register_custom_scripts_options_page).
 * Three free-text fields (head/body/footer) that get echoed verbatim on
 * wp_head/wp_body_open/wp_footer by output_custom_head_scripts() and co. in
 * the same file - for things like Google Tag Manager, tracking pixels or
 * verification meta tags, without a code deploy.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'    => 'group_ajustes_scripts',
  'title'  => __( 'Ajustes de Scripts', 'air-light' ),
  'fields' => [
    [
      'key'          => 'field_scripts_head',
      'label'        => __( 'Head', 'air-light' ),
      'name'         => 'scripts_head',
      'type'         => 'textarea',
      'instructions' => __( 'Se inserta justo antes de </head> en todas las páginas. Ej. Google Tag Manager, verificación de sitio, meta tags.', 'air-light' ),
      'rows'         => 8,
      'new_lines'    => '',
    ],
    [
      'key'          => 'field_scripts_body',
      'label'        => __( 'Body', 'air-light' ),
      'name'         => 'scripts_body',
      'type'         => 'textarea',
      'instructions' => __( 'Se inserta justo después de la etiqueta <body> en todas las páginas. Ej. el <noscript> de Google Tag Manager.', 'air-light' ),
      'rows'         => 8,
      'new_lines'    => '',
    ],
    [
      'key'          => 'field_scripts_footer',
      'label'        => __( 'Footer', 'air-light' ),
      'name'         => 'scripts_footer',
      'type'         => 'textarea',
      'instructions' => __( 'Se inserta justo antes de </body> en todas las páginas. Ej. píxeles de conversión, chats, scripts de analítica.', 'air-light' ),
      'rows'         => 8,
      'new_lines'    => '',
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'options_page',
        'operator' => '==',
        'value'    => 'ajustes-scripts',
      ],
    ],
  ],
] );
