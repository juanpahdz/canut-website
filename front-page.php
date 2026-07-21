<?php
/**
 * The template for displaying front page
 *
 * Content comes from the block editor: edit the Front page and fill in the
 * "Página de inicio CANUT" block (template-parts/blocks/homepage-canut.php)
 * to change any homepage text, image or link.
 *
 * @package air-light
 */

namespace Air_Light;

get_header(); ?>

<main class="site-main home-canut">
  <?php
    the_content();
    air_edit_link();
  ?>
</main>

<?php get_footer();
