<?php
/**
 * Garantía: Section 3 - Exclusiones de Garantía (full-width dark band with a
 * 2x2 card grid).
 *
 * Fed by template-parts/blocks/garantia-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$title = $args['title'] ?? __( 'Exclusiones de Garantía', 'air-light' );
$text  = $args['text'] ?? __( 'Nuestra promesa de excelencia es sólida, pero existen condiciones que invalidan la garantía para proteger la integridad de nuestros procesos artesanales:', 'air-light' );
$items = $args['items'] ?? [];

?>

<section class="garantia-exclusions">
  <div class="wrap-canut">
    <div class="garantia-exclusions-inner">
      <div class="garantia-exclusions-header">
        <span class="garantia-exclusions-icon">
          <?php require get_theme_file_path( 'assets/svg/icon-x.svg' ); ?>
        </span>
        <h2 class="garantia-exclusions-title"><?php echo esc_html( $title ); ?></h2>
      </div>

      <p class="garantia-exclusions-text"><?php echo esc_html( $text ); ?></p>

      <div class="garantia-exclusions-grid">
        <?php foreach ( $items as $item ) : ?>
          <div class="garantia-exclusions-item">
            <?php require get_theme_file_path( 'assets/svg/icon-x.svg' ); ?>
            <div class="garantia-exclusions-item-body">
              <p class="garantia-exclusions-item-title"><?php echo esc_html( $item['title'] ); ?></p>
              <p class="garantia-exclusions-item-text"><?php echo esc_html( $item['text'] ); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
