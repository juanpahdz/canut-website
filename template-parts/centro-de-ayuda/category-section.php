<?php
/**
 * Centro de ayuda: one category's FAQ accordion + WhatsApp CTA band.
 *
 * Content expands inline (no navigation away from /centro-de-ayuda) using
 * the existing accordion-canut component (modules/accordion-canut.js,
 * sass/components/_accordion-canut.scss) - each resource also keeps its own
 * permalink (single-recurso_ayuda.php) for direct links/SEO/sharing.
 *
 * @param array $args {
 *   @type WP_Term $term Categoria_Ayuda term.
 * }
 *
 * @package air-light
 */

namespace Air_Light;

$term = $args['term'] ?? null;

if ( ! $term instanceof \WP_Term ) {
  return;
}

$resources = new \WP_Query( [
  'post_type'      => 'recurso_ayuda',
  'post_status'    => 'publish',
  'posts_per_page' => -1,
  'orderby'        => [ 'menu_order' => 'ASC', 'title' => 'ASC' ],
  'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- small, curated taxonomy, not user input.
    [
      'taxonomy' => 'categoria_ayuda',
      'field'    => 'term_id',
      'terms'    => $term->term_id,
    ],
  ],
] );

if ( ! $resources->have_posts() ) {
  return;
}

$term_ref = "{$term->taxonomy}_{$term->term_id}";
$icon     = get_field( 'icono', $term_ref ) ?: 'chat-circle';
$icon_path = get_theme_file_path( "assets/svg/icon-{$icon}.svg" );

$cta_title = get_field( 'cta_titulo', $term_ref ) ?: sprintf(
  /* translators: %s: category name, lowercased (e.g. "el producto") */
  __( '¿Aún más dudas sobre %s?', 'air-light' ),
  mb_strtolower( $term->name )
);
$cta_description  = get_field( 'cta_descripcion', $term_ref ) ?: __( 'Habla con un especialista en tecnología CANUT ahora mismo.', 'air-light' );
$cta_message      = get_field( 'cta_whatsapp_mensaje', $term_ref );
$cta_whatsapp_url = 'https://wa.me/' . ( $cta_message ? '?text=' . rawurlencode( $cta_message ) : '' );

?>

<section id="categoria-<?php echo esc_attr( $term->slug ); ?>" class="help-center-category" data-help-center-category="<?php echo esc_attr( $term->slug ); ?>">
  <div class="help-center-category-heading">
    <span class="help-center-category-icon">
      <?php if ( file_exists( $icon_path ) ) : require $icon_path;
      endif; ?>
    </span>
    <h2><?php echo esc_html( $term->name ); ?></h2>
  </div>

  <div class="help-center-faq-list">
    <?php
    while ( $resources->have_posts() ) :
      $resources->the_post();
      ?>
      <details class="accordion-canut-item" data-help-center-resource="<?php the_ID(); ?>">
        <summary class="accordion-canut-trigger">
          <span><?php the_title(); ?></span>
          <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
        </summary>
        <div class="accordion-canut-panel">
          <?php the_content(); ?>
        </div>
      </details>
      <?php
    endwhile;
    wp_reset_postdata();
    ?>
  </div>

  <div class="help-center-cta-band">
    <div class="help-center-cta-band-content">
      <h3><?php echo esc_html( $cta_title ); ?></h3>
      <p><?php echo esc_html( $cta_description ); ?></p>
    </div>
    <a
      href="<?php echo esc_url( $cta_whatsapp_url ); ?>"
      class="button-canut-base button-canut-secondary help-center-cta-band-link"
      target="_blank"
      rel="noopener noreferrer"
    >
      <?php require get_theme_file_path( 'assets/svg/icon-whatsapp.svg' ); ?>
      <?php esc_html_e( 'Escríbenos por WhatsApp', 'air-light' ); ?>
    </a>
  </div>
</section>
