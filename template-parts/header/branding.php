<?php
/**
 * Site branding & logo
 *
 * @package air-light
 */

namespace Air_Light;

$description = get_bloginfo( 'description', 'display' );

// The front page header floats transparent over a dark hero (see .home
// .site-header in _site-header.scss), so it needs the white logo variant
// instead of the default dark one used on every other page.
$logo = is_front_page() ? THEME_SETTINGS['logo_white'] : THEME_SETTINGS['logo'];
?>

<div class="site-branding">

  <p class="site-title">
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
      <span class="screen-reader-text"><?php bloginfo( 'name' ); ?></span>
      <?php require get_theme_file_path( $logo ); ?>
    </a>
  </p>

  <?php if ( $description || is_customize_preview() ) : ?>
    <p class="site-description screen-reader-text">
      <?php echo esc_html( $description ); ?>
    </p>
  <?php endif; ?>

</div>
