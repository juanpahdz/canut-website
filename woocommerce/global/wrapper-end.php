<?php
/**
 * Content wrapper end.
 *
 * Overridden to close the theme's own <main> markup opened in
 * global/wrapper-start.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

echo '</main>';
