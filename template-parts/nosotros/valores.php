<?php
/**
 * Nosotros: Section 3 - Diferenciales (bento grid de 4 tarjetas).
 *
 * Fed by template-parts/blocks/nosotros-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * Card layout (image/stat/tags/link) is driven by which optional fields
 * each card has set, not by its position, so editors can reorder or swap
 * cards in ACF without breaking the markup.
 *
 * @package air-light
 */

namespace Air_Light;

$eyebrow = $args['eyebrow'] ?? __( 'Pilares fundamentales', 'air-light' );
$title   = $args['title'] ?? __( 'Nuestros Diferenciales', 'air-light' );
$cards   = $args['cards'] ?? [];

?>

<section class="nosotros-valores">
  <div class="wrap-canut nosotros-valores-inner">
    <div class="nosotros-valores-header">
      <span class="nosotros-valores-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
      <h2 class="nosotros-valores-title"><?php echo esc_html( $title ); ?></h2>
    </div>

    <div class="nosotros-valores-grid">
      <?php foreach ( $cards as $card ) : ?>
        <?php
        $variant = $card['variant'] ?? 'light';
        $has_media = ! empty( $card['image']['url'] );
        ?>
        <div class="nosotros-card is-<?php echo esc_attr( $variant ); ?><?php echo $has_media ? ' has-media' : ''; ?>">
          <div class="nosotros-card-body">
            <?php if ( ! empty( $card['icon'] ) ) : ?>
              <span class="nosotros-card-icon">
                <?php require get_theme_file_path( 'assets/svg/icon-' . $card['icon'] . '.svg' ); ?>
              </span>
            <?php endif; ?>

            <h3 class="nosotros-card-title"><?php echo esc_html( $card['title'] ?? '' ); ?></h3>
            <p class="nosotros-card-text"><?php echo esc_html( $card['text'] ?? '' ); ?></p>

            <?php if ( ! empty( $card['tags'] ) ) : ?>
              <ul class="nosotros-card-tags">
                <?php foreach ( $card['tags'] as $tag ) : ?>
                  <li class="nosotros-card-tag"><?php echo esc_html( $tag ); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if ( ! empty( $card['link_label'] ) ) : ?>
              <span class="nosotros-card-link">
                <?php echo esc_html( $card['link_label'] ); ?>
                <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
              </span>
            <?php endif; ?>

            <?php if ( ! empty( $card['stat_value'] ) ) : ?>
              <div class="nosotros-card-stat">
                <span class="nosotros-card-stat-value"><?php echo esc_html( $card['stat_value'] ); ?></span>
                <?php if ( ! empty( $card['stat_label'] ) ) : ?>
                  <span class="nosotros-card-stat-label"><?php echo esc_html( $card['stat_label'] ); ?></span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

          <?php if ( $has_media ) : ?>
            <div class="nosotros-card-media">
              <img
                src="<?php echo esc_url( $card['image']['url'] ); ?>"
                alt="<?php echo esc_attr( $card['image']['alt'] ?? '' ); ?>"
                loading="lazy"
              >
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
