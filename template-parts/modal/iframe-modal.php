<?php
/**
 * Iframe modal shell: a <dialog> that loads a page in an <iframe> instead of
 * navigating to it. Triggered by any link carrying data-canut-iframe-modal
 * (modules/iframe-modal-canut.js), e.g. the checkout footer's Soporte /
 * Política de devoluciones / Envíos links (footer.php).
 *
 * Included once, site-wide, from footer.php.
 *
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

?>

<dialog id="iframe-modal-canut" class="iframe-modal-canut" aria-label="<?php esc_attr_e( 'Contenido', 'air-light' ); ?>">

  <div class="iframe-modal-canut-header">
    <h2 class="iframe-modal-canut-title" id="iframe-modal-canut-title"></h2>
    <button type="button" class="iframe-modal-canut-close" data-canut-iframe-close aria-label="<?php esc_attr_e( 'Cerrar', 'air-light' ); ?>">
      <?php require get_theme_file_path( 'assets/svg/icon-x.svg' ); ?>
    </button>
  </div>

  <div class="iframe-modal-canut-body">
    <iframe id="iframe-modal-canut-frame" title=""></iframe>
  </div>

</dialog>
