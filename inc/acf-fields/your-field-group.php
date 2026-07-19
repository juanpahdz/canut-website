<?php
/**
 * Example ACF field group, registered via PHP.
 *
 * Copy this file, rename it after your field group and require it from
 * inc/acf-fields.php. Easiest way to get the array below is building the
 * field group in ACF UI, then using its "Export" > "Generate PHP" screen
 * and pasting the result here.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

acf_add_local_field_group( [
  'key'      => 'group_your_field_group',
  'title'    => 'Your Field Group',
  'fields'   => [
    [
      'key'   => 'field_your_field_group_example',
      'label' => 'Example field',
      'name'  => 'example_field',
      'type'  => 'text',
    ],
  ],
  'location' => [
    [
      [
        'param'    => 'post_type',
        'operator' => '==',
        'value'    => 'page',
      ],
    ],
  ],
] );
