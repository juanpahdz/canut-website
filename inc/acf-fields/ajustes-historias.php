<?php
/**
 * Field group for the "Ajustes de Historias" options page, registered in
 * inc/hooks/historia.php (register_historia_options_page). Holds the
 * Cloudflare Turnstile credentials the public "Cuenta tu historia" form
 * (template-parts/historia/submit-form.php) needs to verify a submission
 * isn't a bot before creating a draft Historia post.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'      => 'group_ajustes_historias',
  'title'    => __( 'Ajustes de Historias', 'air-light' ),
  'fields'   => [
    [
      'key'           => 'field_historia_turnstile_enabled',
      'label'         => __( 'Activar captcha', 'air-light' ),
      'name'          => 'turnstile_enabled',
      'type'          => 'true_false',
      'instructions'  => __( 'Exige verificación de Cloudflare Turnstile en el formulario público "Cuenta tu historia". Si está desactivado, cualquiera puede enviar una historia sin verificación.', 'air-light' ),
      'default_value' => 0,
      'ui'            => 1,
    ],
    [
      'key'          => 'field_historia_turnstile_site_key',
      'label'        => __( 'Turnstile Site Key', 'air-light' ),
      'name'         => 'turnstile_site_key',
      'type'         => 'text',
      'instructions' => __( 'Clave pública, del panel de Cloudflare (Turnstile > tu sitio).', 'air-light' ),
      'wrapper'      => [ 'width' => '50' ],
    ],
    [
      'key'          => 'field_historia_turnstile_secret_key',
      'label'        => __( 'Turnstile Secret Key', 'air-light' ),
      'name'         => 'turnstile_secret_key',
      'type'         => 'password',
      'instructions' => __( 'Clave secreta, del panel de Cloudflare (Turnstile > tu sitio).', 'air-light' ),
      'wrapper'      => [ 'width' => '50' ],
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'options_page',
        'operator' => '==',
        'value'    => 'ajustes-historias',
      ],
    ],
  ],
] );
