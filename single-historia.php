<?php
/**
 * The template for displaying a single Historia.
 *
 * @package air-light
 */

namespace Air_Light;

the_post();
get_header();
?>

<main class="site-main historia-single">
  <div class="wrap-canut historia-single-inner">
    <ul class="historia-list">
      <?php
      $historia_id = get_the_ID();
      get_template_part( 'template-parts/historia/card', '', [
        'image'  => [ 'url' => get_the_post_thumbnail_url( $historia_id, 'medium' ), 'alt' => get_the_title() ],
        'rating' => (int) ( get_field( 'rating', $historia_id ) ?: 5 ),
        'quote'  => get_field( 'quote', $historia_id ),
        'author' => get_the_title(),
      ] );
      ?>
    </ul>

    <a href="<?php echo esc_url( get_post_type_archive_link( 'historia' ) ); ?>" class="button-canut-base button-canut-secondary">
      <?php esc_html_e( 'Ver todas las historias', 'air-light' ); ?>
    </a>
  </div>
</main>

<?php get_footer();
