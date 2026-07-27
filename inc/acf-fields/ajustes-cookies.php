<?php
/**
 * Field group for the "Ajustes de Cookies" options page, registered in
 * inc/hooks/cookie-notice.php (register_cookie_notice_options_page). Drives
 * the site-wide cookie notice rendered on every page (render_cookie_notice(),
 * same file) - text and button label editable without a code deploy.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'      => 'group_ajustes_cookies',
  'title'    => __( 'Ajustes de Cookies', 'air-light' ),
  'fields'   => [
    [
      'key'           => 'field_cookie_notice_enabled',
      'label'         => __( 'Activar aviso de cookies', 'air-light' ),
      'name'          => 'cookie_notice_enabled',
      'type'          => 'true_false',
      'instructions'  => __( 'Muestra el aviso de cookies en todas las páginas hasta que el visitante lo acepte.', 'air-light' ),
      'default_value' => 1,
      'ui'            => 1,
    ],
    [
      'key'           => 'field_cookie_notice_text',
      'label'         => __( 'Texto del aviso', 'air-light' ),
      'name'          => 'cookie_notice_text',
      'type'          => 'textarea',
      'default_value' => __( 'Usamos cookies propias y de terceros para mejorar tu experiencia de navegación, analizar el tráfico del sitio y mostrarte publicidad relevante. Puedes conocer más en nuestra política de cookies.', 'air-light' ),
      'rows'          => 3,
      'new_lines'     => '',
    ],
    [
      'key'           => 'field_cookie_notice_button_label',
      'label'         => __( 'Texto del botón', 'air-light' ),
      'name'          => 'cookie_notice_button_label',
      'type'          => 'text',
      'default_value' => __( 'Aceptar', 'air-light' ),
      'wrapper'       => [ 'width' => '50' ],
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'options_page',
        'operator' => '==',
        'value'    => 'ajustes-cookies',
      ],
    ],
  ],
] );
