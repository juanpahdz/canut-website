<?php
/**
 * The template for displaying the "Nosotros" page
 *
 * Applies automatically to the WordPress Page with slug `nosotros` (WP
 * template hierarchy: page-{slug}.php). Content comes from the block
 * editor: edit the page and fill in the "Página Nosotros CANUT" block
 * (template-parts/blocks/nosotros-canut.php) to change any text, image or
 * link.
 *
 * Unlike the default page.php, <main> here carries neither
 * `has-global-padding` nor `is-layout-constrained`: this page's sections
 * are full-bleed (hero photo, mint-tinted bands) with their own internal
 * `.wrap-canut` constraining just the text - same reasoning as
 * front-page.php, which skips those classes for the same kind of one-block
 * marketing page.
 *
 * @package air-light
 */

namespace Air_Light;

the_post();

get_header(); ?>

<main class="site-main nosotros-canut">
  <?php
    the_content();
    air_edit_link();
  ?>
</main>

<?php get_footer();
