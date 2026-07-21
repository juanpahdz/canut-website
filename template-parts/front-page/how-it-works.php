<?php
/**
 * Front page: Section 5 - Cómo funciona.
 *
 * Fed by template-parts/blocks/homepage-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$title = $args['title'] ?? __( 'Simplicidad en cada detalle', 'air-light' );
$steps = $args['steps'] ?? [
  [
    'image' => [ 'url' => get_theme_file_uri( 'assets/images/homepage/how-it-works-1.jpg' ), 'alt' => __( 'Persona configurando la app CANUT desde su celular', 'air-light' ) ],
    'title' => __( '01. Configura', 'air-light' ),
    'text'  => __( 'Define horarios y porciones exactas desde nuestra App intuitiva.', 'air-light' ),
  ],
  [
    'image' => [ 'url' => get_theme_file_uri( 'assets/images/homepage/how-it-works-2.jpg' ), 'alt' => __( 'Comedero CANUT dispensando alimento en un plato', 'air-light' ) ],
    'title' => __( '02. Sirve', 'air-light' ),
    'text'  => __( 'El mecanismo silencioso entrega el alimento con precisión gramatical.', 'air-light' ),
  ],
  [
    'image' => [ 'url' => get_theme_file_uri( 'assets/images/homepage/how-it-works-3.jpg' ), 'alt' => __( 'Mujer acariciando a su perro junto al comedero CANUT', 'air-light' ) ],
    'title' => __( '03. Disfruta', 'air-light' ),
    'text'  => __( 'Tu mascota está feliz y tú tienes la tranquilidad que se merece.', 'air-light' ),
  ],
];

?>

<section class="home-how-it-works">
  <div class="wrap-canut home-how-it-works-inner">
    <h2 class="home-how-it-works-title"><?php echo esc_html( $title ); ?></h2>

    <ol class="home-how-it-works-list">
      <?php foreach ( $steps as $step ) : ?>
        <li class="home-how-it-works-item">
          <div class="home-how-it-works-media">
            <img
              src="<?php echo esc_url( $step['image']['url'] ); ?>"
              alt="<?php echo esc_attr( $step['image']['alt'] ); ?>"
              loading="lazy"
            >
          </div>
          <p class="home-how-it-works-step"><?php echo esc_html( $step['title'] ); ?></p>
          <p class="home-how-it-works-text"><?php echo esc_html( $step['text'] ); ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
