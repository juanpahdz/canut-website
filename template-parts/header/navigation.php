<?php
/**
 * Navigation layout.
 *
 * @package air-light
 */

namespace Air_Light;

?>

<nav id="nav" class="nav-primary nav-menu" aria-label="<?php echo esc_html( get_default_localization( 'Main navigation' ) ); ?>">

  <button aria-haspopup="true" aria-expanded="false" aria-controls="nav" id="nav-toggle" class="nav-toggle" type="button" aria-label="<?php echo esc_html( get_default_localization( 'Open main menu' ) ); ?>">
    <span class="hamburger" aria-hidden="true"></span>
  </button>

  <div id="menu-items-overlay" class="menu-items-overlay"></div>

  <div id="menu-items-wrapper" class="menu-items-wrapper">

    <p class="menu-items-wrapper-branding">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
        <span class="screen-reader-text"><?php bloginfo( 'name' ); ?></span>
        <?php require get_theme_file_path( THEME_SETTINGS['logo'] ); ?>
      </a>
    </p>

    <?php wp_nav_menu( array(
      'theme_location' => 'primary',
      'container'      => false,
      'depth'          => 4,
      'menu_class'     => 'menu-items',
      'menu_id'        => 'main-menu',
      'echo'           => true,
      'fallback_cb'    => __NAMESPACE__ . '\Nav_Walker::fallback',
      'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
      'has_dropdown'   => true,
      'walker'         => new Nav_Walker(),
    ) );
    ?>

    <div class="menu-items-wrapper-contact">
      <a href="<?php echo esc_url( home_url( '/contacto/' ) ); ?>" class="button-canut-base button-canut-primary is-full-width">
        <?php echo esc_html__( 'Contáctanos', 'air-light' ); ?>
      </a>
    </div>

  </div>

</nav>
