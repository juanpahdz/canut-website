<?php
/**
 * The template for displaying the Historia archive ("Ver todas" from the front page).
 *
 * @package air-light
 */

namespace Air_Light;

get_header(); ?>

<main class="site-main historia-archive">
  <div class="wrap-canut historia-archive-inner">
    <h1 class="historia-archive-title"><?php post_type_archive_title(); ?></h1>

    <?php if ( have_posts() ) : ?>
      <ul class="historia-list">
        <?php
        while ( have_posts() ) :
          the_post();
          get_template_part( 'template-parts/historia/card', '', historia_get_card_args( get_the_ID() ) );
        endwhile;
        ?>
      </ul>

      <?php the_posts_pagination(); ?>
    <?php else : ?>
      <p><?php esc_html_e( 'Todavía no hay historias publicadas.', 'air-light' ); ?></p>
    <?php endif; ?>

    <div class="historia-archive-cta">
      <div class="historia-archive-cta-content">
        <h2><?php esc_html_e( '¿Ya tienes un CANUT?', 'air-light' ); ?></h2>
        <p><?php esc_html_e( 'Cuenta tu historia y ayuda a otros dueños a decidirse.', 'air-light' ); ?></p>
      </div>
      <button type="button" class="button-canut-base button-canut-light is-no-arrow" data-canut-historia-open>
        <?php esc_html_e( 'Cuenta tu historia', 'air-light' ); ?>
      </button>
    </div>
  </div>
</main>

<?php get_footer();
