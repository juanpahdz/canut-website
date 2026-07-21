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
          $historia_id = get_the_ID();
          get_template_part( 'template-parts/historia/card', '', [
            'image'  => [ 'url' => get_the_post_thumbnail_url( $historia_id, 'medium' ), 'alt' => get_the_title() ],
            'rating' => (int) ( get_field( 'rating', $historia_id ) ?: 5 ),
            'quote'  => get_field( 'quote', $historia_id ),
            'author' => get_the_title(),
          ] );
        endwhile;
        ?>
      </ul>

      <?php the_posts_pagination(); ?>
    <?php else : ?>
      <p><?php esc_html_e( 'Todavía no hay historias publicadas.', 'air-light' ); ?></p>
    <?php endif; ?>
  </div>
</main>

<?php get_footer();
