<?php
/**
 * The template for displaying the "Garantía" page
 *
 * Applies automatically to the WordPress Page with slug `garantia` (WP
 * template hierarchy: page-{slug}.php, same convention as page-nosotros.php
 * / page-contacto.php). Content comes from the block editor: edit the page
 * and fill in the "Página Garantía CANUT" block
 * (template-parts/blocks/garantia-canut.php) to change any text, icon or
 * repeater row.
 *
 * Unlike the default page.php, <main> here carries neither
 * `has-global-padding` nor `is-layout-constrained`: this page's sections are
 * full-bleed (hero photo, dark exclusiones band) with their own internal
 * `.wrap-canut` constraining just the text - same reasoning as
 * page-nosotros.php / page-contacto.php.
 *
 * @package air-light
 */

namespace Air_Light;

the_post();

get_header(); ?>

<main class="site-main garantia-canut">
  <?php
    the_content();
    air_edit_link();
  ?>
</main>

<?php get_footer();
