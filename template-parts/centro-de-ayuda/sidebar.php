<?php
/**
 * Centro de ayuda: category sidebar navigation (desktop).
 *
 * Anchor links into each #categoria-{slug} section rendered by
 * category-section.php. modules/help-center-canut.js highlights the link
 * for whichever category is currently in view.
 *
 * @param array $args {
 *   @type WP_Term[] $terms Categoria_Ayuda terms, in display order.
 * }
 *
 * @package air-light
 */

namespace Air_Light;

$terms = $args['terms'] ?? [];

if ( empty( $terms ) ) {
  return;
}

?>

<aside class="help-center-sidebar" data-help-center-sidebar>
  <h2 class="help-center-sidebar-heading">
    <?php esc_html_e( 'Categorías', 'air-light' ); ?>
  </h2>

  <nav class="help-center-sidebar-nav">
    <?php foreach ( $terms as $term ) : ?>
      <a
        href="#categoria-<?php echo esc_attr( $term->slug ); ?>"
        class="help-center-sidebar-link"
        data-help-center-sidebar-link="<?php echo esc_attr( $term->slug ); ?>"
      >
        <?php echo esc_html( $term->name ); ?>
      </a>
    <?php endforeach; ?>
  </nav>
</aside>
