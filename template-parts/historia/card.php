<?php
/**
 * Single historia card, shared by the front page carousel and the historia archive.
 *
 * Expects $args: image (array: url, alt), rating (int 1-5), quote (string), author (string).
 *
 * @package air-light
 */

namespace Air_Light;

$image  = $args['image'] ?? [];
$rating = (int) ( $args['rating'] ?? 5 );
$quote  = $args['quote'] ?? '';
$author = $args['author'] ?? '';

?>

<li class="historia-card">
  <?php if ( ! empty( $image['url'] ) ) : ?>
    <div class="historia-card-media">
      <img
        src="<?php echo esc_url( $image['url'] ); ?>"
        alt="<?php echo esc_attr( $image['alt'] ?? '' ); ?>"
        loading="lazy"
      >
    </div>
  <?php endif; ?>
  <div class="historia-card-rating" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: rating out of 5 */ __( '%d de 5 estrellas', 'air-light' ), $rating ) ); ?>">
    <?php for ( $star = 0; $star < $rating; $star++ ) : ?>
      <?php require get_theme_file_path( 'assets/svg/icon-star.svg' ); ?>
    <?php endfor; ?>
  </div>
  <p class="historia-card-quote">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
  <p class="historia-card-author"><?php echo esc_html( $author ); ?></p>
</li>
