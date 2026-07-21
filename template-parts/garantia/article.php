<?php
/**
 * Garantía: Section 2 - Artículo legal (white card, icon + title + copy per
 * section, an optional highlighted callout and an optional bullet list).
 *
 * Fed by template-parts/blocks/garantia-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$sections = $args['sections'] ?? [];

?>

<section class="garantia-article-wrap">
  <div class="wrap-canut">
    <article class="garantia-article">
      <?php foreach ( $sections as $index => $section ) : ?>
        <?php // A highlighted section (its own bg/border) already reads as separated, so a divider only precedes a section following a plain one. ?>
        <?php if ( $index > 0 && ! $sections[ $index - 1 ]['highlight'] ) : ?>
          <hr class="garantia-article-separator">
        <?php endif; ?>

        <div class="garantia-article-section<?php echo $section['highlight'] ? ' is-highlight' : ''; ?>">
          <div class="garantia-article-section-header">
            <span class="garantia-article-section-icon">
              <?php require get_theme_file_path( 'assets/svg/icon-' . $section['icon'] . '.svg' ); ?>
            </span>
            <h2 class="garantia-article-section-title"><?php echo esc_html( $section['title'] ); ?></h2>
          </div>

          <div class="garantia-article-section-body">
            <?php echo wp_kses_post( $section['content'] ); ?>
          </div>

          <?php if ( ! empty( $section['list_items'] ) ) : ?>
            <ul class="garantia-article-section-list">
              <?php foreach ( $section['list_items'] as $item ) : ?>
                <li class="garantia-article-section-list-item">
                  <?php require get_theme_file_path( 'assets/svg/icon-check.svg' ); ?>
                  <?php echo esc_html( $item ); ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </article>
  </div>
</section>
