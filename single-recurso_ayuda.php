<?php
/**
 * The template for displaying a single Recurso de ayuda.
 *
 * Direct-link/SEO/share counterpart to the accordion entry on the Centro de
 * ayuda hub (page-centro-de-ayuda.php) - same title + WYSIWYG content, its
 * own permalink.
 *
 * @package air-light
 */

namespace Air_Light;

the_post();
get_header();

$terms        = get_the_terms( get_the_ID(), 'categoria_ayuda' );
$primary_term = ( is_array( $terms ) && ! empty( $terms ) ) ? $terms[0] : null;

$hub_page = get_page_by_path( 'centro-de-ayuda' );
$hub_url  = $hub_page ? get_permalink( $hub_page ) : home_url( '/centro-de-ayuda/' );
$back_url = $primary_term ? $hub_url . '#categoria-' . $primary_term->slug : $hub_url;

?>

<main class="site-main single-recurso-ayuda">
  <div class="wrap-canut single-recurso-ayuda-inner">

    <a href="<?php echo esc_url( $back_url ); ?>" class="single-recurso-ayuda-back">
      <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
      <?php esc_html_e( 'Centro de ayuda', 'air-light' ); ?>
    </a>

    <?php if ( $primary_term ) : ?>
      <p class="single-recurso-ayuda-category"><?php echo esc_html( $primary_term->name ); ?></p>
    <?php endif; ?>

    <h1 class="single-recurso-ayuda-title"><?php the_title(); ?></h1>

    <div class="single-recurso-ayuda-content">
      <?php the_content(); ?>
    </div>

  </div>
</main>

<?php get_footer();
