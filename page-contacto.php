<?php
/**
 * The template for displaying the "Contacto" page
 *
 * Applies automatically to the WordPress Page with slug `contacto` (WP
 * template hierarchy: page-{slug}.php). Content comes from the block
 * editor: edit the page and fill in the "Página Contacto CANUT" block
 * (template-parts/blocks/contacto-canut.php) to change any text, email,
 * phone number or map link.
 *
 * Unlike the default page.php, <main> here carries neither
 * `has-global-padding` nor `is-layout-constrained`: this page's section
 * has its own internal `.wrap-canut` constraining just the content - same
 * reasoning as page-nosotros.php.
 *
 * @package air-light
 */

namespace Air_Light;

the_post();

get_header(); ?>

<main class="site-main contacto-canut">
  <?php
    the_content();
    air_edit_link();
  ?>
</main>

<?php get_footer();
