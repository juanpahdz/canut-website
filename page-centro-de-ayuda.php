<?php
/**
 * Template for the Centro de ayuda (help center) hub.
 *
 * Applies automatically to the WordPress Page with slug `centro-de-ayuda`
 * (WP template hierarchy: page-{slug}.php, same convention as
 * page-sistema-de-diseno.php). Lists every Categoria_Ayuda term that has at
 * least one published Recurso_Ayuda, each rendered as an expandable FAQ
 * accordion (template-parts/centro-de-ayuda/category-section.php). The hero
 * search (template-parts/centro-de-ayuda/hero-search.php) filters this grid
 * live via the canut_help_search AJAX action (inc/hooks/help-center.php).
 *
 * @package air-light
 */

namespace Air_Light;

get_header();

$terms = get_terms( [
  'taxonomy'   => 'categoria_ayuda',
  'hide_empty' => true,
] );

if ( is_wp_error( $terms ) ) {
  $terms = [];
}

// No native "term order" for a generic taxonomy - sort by the editorial
// `orden` field (inc/acf-fields/categoria-ayuda.php) instead, terms without
// one sink to the bottom alphabetically.
usort( $terms, function ( $term_a, $term_b ) {
  $order_a = (int) ( get_field( 'orden', "{$term_a->taxonomy}_{$term_a->term_id}" ) ?: 999 );
  $order_b = (int) ( get_field( 'orden', "{$term_b->taxonomy}_{$term_b->term_id}" ) ?: 999 );

  return $order_a === $order_b ? strcasecmp( $term_a->name, $term_b->name ) : $order_a <=> $order_b;
} );

?>

<main class="site-main help-center-canut">

  <?php get_template_part( 'template-parts/centro-de-ayuda/hero-search' ); ?>

  <div class="wrap-canut help-center-layout">

    <?php get_template_part( 'template-parts/centro-de-ayuda/sidebar', null, [ 'terms' => $terms ] ); ?>

    <div class="help-center-categories" data-help-center-categories>
      <?php foreach ( $terms as $term ) : ?>
        <?php get_template_part( 'template-parts/centro-de-ayuda/category-section', null, [ 'term' => $term ] ); ?>
      <?php endforeach; ?>

      <p class="help-center-empty" data-help-center-empty hidden>
        <?php esc_html_e( 'No encontramos resultados para tu búsqueda. Prueba con otra palabra o escríbenos por WhatsApp.', 'air-light' ); ?>
      </p>
    </div>

  </div>

</main>

<?php get_footer();
