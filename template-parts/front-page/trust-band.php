<?php
/**
 * Front page: Section 2 - Trust band.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$items = $args['items'] ?? [
  [ 'icon' => 'shield-check', 'label' => __( 'Garantía de por vida', 'air-light' ) ],
  [ 'icon' => 'truck', 'label' => __( 'Envíos Medellín y Nacional', 'air-light' ) ],
  [ 'icon' => 'chat-circle', 'label' => __( 'Soporte WhatsApp 24/7', 'air-light' ) ],
  [ 'icon' => 'wrench', 'label' => __( 'Excelencia artesanal', 'air-light' ) ],
];

?>

<section class="home-trust-band">
  <div class="wrap-canut home-trust-band-track">
    <ul class="home-trust-band-list">
      <?php foreach ( $items as $item ) : ?>
        <li class="home-trust-band-item">
          <?php require get_theme_file_path( 'assets/svg/icon-' . $item['icon'] . '.svg' ); ?>
          <span><?php echo esc_html( $item['label'] ); ?></span>
        </li>
      <?php endforeach; ?>
      <?php // Duplicate items so the mobile marquee (see _front-page.scss) can loop seamlessly; hidden from assistive tech since it repeats the list above, and hidden visually above the tablet breakpoint where there's no marquee. ?>
      <?php foreach ( $items as $item ) : ?>
        <li class="home-trust-band-item" aria-hidden="true">
          <?php require get_theme_file_path( 'assets/svg/icon-' . $item['icon'] . '.svg' ); ?>
          <span><?php echo esc_html( $item['label'] ); ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
