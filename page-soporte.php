<?php
/**
 * Template for the Soporte (community support) page.
 *
 * Applies automatically to the WordPress Page with slug `soporte` (WP
 * template hierarchy: page-{slug}.php, same convention as
 * page-centro-de-ayuda.php / page-sistema-de-diseno.php). Visitors submit a
 * question via template-parts/soporte/question-form.php (posts to
 * inc/hooks/soporte.php, creates a pending Pregunta_Soporte post); once an
 * editor approves it in wp-admin it's mirrored into a Recurso_Ayuda post
 * and starts appearing both in the "Últimas preguntas" sidebar here and on
 * the Centro de ayuda FAQ listing.
 *
 * @package air-light
 */

namespace Air_Light;

get_header();

$terms = get_terms( [
  'taxonomy'   => 'categoria_ayuda',
  'hide_empty' => false,
] );

if ( is_wp_error( $terms ) ) {
  $terms = [];
}

?>

<main class="site-main soporte-canut">

  <section class="soporte-hero">
    <div class="wrap-canut soporte-hero-inner">
      <h1 class="soporte-hero-title"><?php esc_html_e( 'Comunidad y Soporte', 'air-light' ); ?></h1>
      <p class="soporte-hero-subtitle">
        <?php esc_html_e( 'Un espacio dedicado a la excelencia en el cuidado de tu compañero. Encuentra respuestas, comparte sabiduría y conecta con nuestra comunidad de expertos.', 'air-light' ); ?>
      </p>
    </div>
  </section>

  <div class="wrap-canut soporte-layout">
    <?php get_template_part( 'template-parts/soporte/question-form', null, [ 'terms' => $terms ] ); ?>
    <?php get_template_part( 'template-parts/soporte/recent-questions' ); ?>
  </div>

  <div class="wrap-canut">
    <?php get_template_part( 'template-parts/soporte/help-center-bridge' ); ?>
  </div>

  <?php get_template_part( 'template-parts/soporte/faq-section' ); ?>

</main>

<?php get_footer();
