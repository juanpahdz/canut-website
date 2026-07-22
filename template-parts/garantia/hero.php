<?php
/**
 * Garantía: Section 1 - Hero.
 *
 * Fed by template-parts/blocks/garantia-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$badge_text      = $args['badge_text'] ?? __( 'Respaldo Total', 'air-light' );
$title           = $args['title'] ?? __( 'Garantía', 'air-light' );
$text            = $args['text'] ?? __( 'Tu comedero CANUT está respaldado por nuestra promesa de excelencia y durabilidad. Diseñamos para la eternidad, cuidando cada detalle para el bienestar de tu mascota.', 'air-light' );
$image           = $args['image'] ?? [
  'url' => get_theme_file_uri( 'assets/images/homepage/hero-dog-feeder.jpg' ),
  'alt' => __( 'Comedero CANUT en un ambiente doméstico', 'air-light' ),
];
$video           = $args['video'] ?? null;
$highlight_title = $args['highlight_title'] ?? __( 'Compromiso Canut', 'air-light' );
$highlight_text  = $args['highlight_text'] ?? __( 'Artesanía de alto rendimiento garantizada.', 'air-light' );

?>

<section class="garantia-hero">
  <div class="garantia-hero-media">
    <?php if ( ! empty( $video['url'] ) ) : ?>
      <video autoplay muted loop playsinline preload="auto" poster="<?php echo esc_url( $image['url'] ); ?>">
        <source src="<?php echo esc_url( $video['url'] ); ?>" <?php echo $video['mime_type'] ? 'type="' . esc_attr( $video['mime_type'] ) . '"' : ''; ?>>
      </video>
    <?php else : ?>
      <img
        src="<?php echo esc_url( $image['url'] ); ?>"
        alt="<?php echo esc_attr( $image['alt'] ); ?>"
        loading="eager"
        fetchpriority="high"
      >
    <?php endif; ?>
  </div>

  <div class="wrap-canut garantia-hero-wrap">
    <div class="garantia-hero-content">
      <span class="garantia-hero-badge">
        <?php require get_theme_file_path( 'assets/svg/icon-shield-check.svg' ); ?>
        <?php echo esc_html( $badge_text ); ?>
      </span>

      <h1 class="garantia-hero-title"><?php echo esc_html( $title ); ?></h1>
      <p class="garantia-hero-text"><?php echo esc_html( $text ); ?></p>

      <div class="garantia-hero-highlight">
        <?php require get_theme_file_path( 'assets/svg/icon-medal.svg' ); ?>
        <div class="garantia-hero-highlight-body">
          <p class="garantia-hero-highlight-title"><?php echo esc_html( $highlight_title ); ?></p>
          <p class="garantia-hero-highlight-text"><?php echo esc_html( $highlight_text ); ?></p>
        </div>
      </div>
    </div>
  </div>
</section>
