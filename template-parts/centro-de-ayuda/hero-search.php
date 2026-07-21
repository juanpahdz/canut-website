<?php
/**
 * Centro de ayuda: hero heading + search field.
 *
 * The <form> is a real GET search (falls back to native WordPress search,
 * scoped to recurso_ayuda, if JS fails to load). modules/help-center-canut.js
 * progressively enhances it into a live, debounced filter of the FAQ grid
 * below via the canut_help_search AJAX action (inc/hooks/help-center.php).
 *
 * @package air-light
 */

namespace Air_Light;

?>

<section class="help-center-hero">
  <div class="wrap-canut help-center-hero-inner">
    <h1 class="help-center-hero-title">
      <?php esc_html_e( '¿Cómo podemos ayudarte?', 'air-light' ); ?>
    </h1>

    <p class="help-center-hero-subtitle">
      <?php esc_html_e( 'Encuentra respuestas rápidas sobre tecnología CANUT, envíos y el bienestar de tu mascota. Nuestro equipo de expertos está siempre a un clic de distancia.', 'air-light' ); ?>
    </p>

    <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="help-center-search" data-help-center-search>
      <?php require get_theme_file_path( 'assets/svg/icon-magnifying-glass.svg' ); ?>

      <input
        type="search"
        name="s"
        class="input-canut help-center-search-input"
        placeholder="<?php echo esc_attr__( "Busca por 'ansiedad', 'garantía' o 'envíos'...", 'air-light' ); ?>"
        autocomplete="off"
        data-help-center-search-input
      >
      <input type="hidden" name="post_type" value="recurso_ayuda">
    </form>
  </div>
</section>
