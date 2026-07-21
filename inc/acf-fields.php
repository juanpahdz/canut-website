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
