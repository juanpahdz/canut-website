<?php
/**
 * Custom ACF field groups, registered via PHP with acf_add_local_field_group().
 *
 * Every field group lives in its own file under inc/acf-fields/, require it
 * below. This file is only loaded when Advanced Custom Fields is active,
 * see inc/hooks.php, so nothing breaks if the plugin is missing.
 *
 * See inc/acf-fields/your-field-group.php for an example to copy.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Require every custom ACF field group file. Hooked to acf/init (see
 * inc/hooks.php) instead of run immediately - each field group calls
 * translation functions (__()) inline, and doing that before WordPress's
 * init action fires triggers WP 6.7's _load_textdomain_just_in_time notice.
 */
function register_acf_field_groups() {
  // require get_theme_file_path( '/inc/acf-fields/your-field-group.php' );
  require get_theme_file_path( '/inc/acf-fields/homepage-canut.php' );
  require get_theme_file_path( '/inc/acf-fields/historia.php' );
  require get_theme_file_path( '/inc/acf-fields/product-canut.php' );
  require get_theme_file_path( '/inc/acf-fields/categoria-ayuda.php' );
  require get_theme_file_path( '/inc/acf-fields/recurso-ayuda.php' );
  require get_theme_file_path( '/inc/acf-fields/pregunta-soporte.php' );
  require get_theme_file_path( '/inc/acf-fields/nosotros-canut.php' );
  require get_theme_file_path( '/inc/acf-fields/pagina-informativa.php' );
  require get_theme_file_path( '/inc/acf-fields/contacto-canut.php' );
  require get_theme_file_path( '/inc/acf-fields/garantia-canut.php' );
  require get_theme_file_path( '/inc/acf-fields/ajustes-whatsapp.php' );
  require get_theme_file_path( '/inc/acf-fields/ajustes-scripts.php' );
  require get_theme_file_path( '/inc/acf-fields/ajustes-facebook-pixel.php' );
  require get_theme_file_path( '/inc/acf-fields/ajustes-cookies.php' );
} // end register_acf_field_groups
