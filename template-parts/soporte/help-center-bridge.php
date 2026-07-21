<?php
/**
 * Soporte: dark CTA band bridging to the Centro de ayuda (help center) hub.
 *
 * @package air-light
 */

namespace Air_Light;

$hub_page = get_page_by_path( 'centro-de-ayuda' );
$hub_url  = $hub_page ? get_permalink( $hub_page ) : home_url( '/centro-de-ayuda/' );

?>

<section class="soporte-bridge">
  <div class="soporte-bridge-content">
    <h2><?php esc_html_e( '¿Buscas respuestas inmediatas?', 'air-light' ); ?></h2>
    <p><?php esc_html_e( 'Explora nuestra biblioteca técnica, guías de cuidado y artículos detallados sobre el ecosistema CANUT en nuestro Centro de Ayuda.', 'air-light' ); ?></p>
  </div>
  <a href="<?php echo esc_url( $hub_url ); ?>" class="button-canut-base button-canut-light is-no-arrow soporte-bridge-link">
    <?php esc_html_e( 'Conoce nuestro centro de ayuda', 'air-light' ); ?>
    <?php require get_theme_file_path( 'assets/svg/icon-arrow-square-out.svg' ); ?>
  </a>
</section>
