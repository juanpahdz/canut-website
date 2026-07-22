<?php
/**
 * Template for header
 *
 * <head> section and everything up until <div id="content">
 *
 * @package air-light
 */

namespace Air_Light;

?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <link rel="profile" href="http://gmpg.org/xfn/11">

  <link rel="icon" href="<?php echo esc_url( get_theme_file_uri( 'assets/svg/favicon.svg' ) ); ?>" type="image/svg+xml">
  <link rel="icon" href="<?php echo esc_url( get_theme_file_uri( 'assets/images/favicon/favicon-32x32.png' ) ); ?>" sizes="32x32" type="image/png">
  <link rel="icon" href="<?php echo esc_url( get_theme_file_uri( 'assets/images/favicon/favicon-16x16.png' ) ); ?>" sizes="16x16" type="image/png">
  <link rel="apple-touch-icon" href="<?php echo esc_url( get_theme_file_uri( 'assets/images/favicon/apple-touch-icon.png' ) ); ?>">

  <?php wp_head(); ?>
</head>

<body <?php body_class( 'no-js' ); ?>>
  <a class="skip-link screen-reader-text" href="#content"><?php echo esc_html( get_default_localization( 'Skip to content' ) ); ?></a>

  <?php wp_body_open(); ?>
  <div id="page" class="site">

    <?php if ( function_exists( 'is_checkout' ) && is_checkout() ) : ?>

      <header class="site-header-checkout">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
          <span class="screen-reader-text"><?php bloginfo( 'name' ); ?></span>
          <?php require get_theme_file_path( THEME_SETTINGS['logo'] ); ?>
        </a>
      </header>

    <?php else : ?>

      <header class="site-header">
        <div class="site-header-inner">
          <?php get_template_part( 'template-parts/header/branding' ); ?>
          <?php get_template_part( 'template-parts/header/navigation' ); ?>
          <?php get_template_part( 'template-parts/header/actions' ); ?>
        </div>
      </header>

    <?php endif; ?>

    <div class="site-content">
