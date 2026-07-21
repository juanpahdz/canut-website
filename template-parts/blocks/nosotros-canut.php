<?php
/**
 * Block: Página Nosotros CANUT.
 *
 * One block renders the entire "Nosotros" page. Every section below is fed
 * by the ACF fields registered in inc/acf-fields/nosotros-canut.php -
 * editing the Nosotros page means filling in this block's fields. Each
 * field falls back to the original CANUT design copy/imagery when left
 * empty.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Returns an ACF image field's url/alt, falling back to a theme-bundled
 * default image when the field is empty.
 *
 * @param array|null $image ACF image field value (return_format: array).
 * @param string     $fallback_file Filename inside assets/images/nosotros/.
 * @param string     $fallback_alt Alt text to use with the fallback image.
 * @return array{url: string, alt: string}
 */
function nosotros_canut_image( $image, $fallback_file, $fallback_alt ) {
  if ( ! empty( $image['url'] ) ) {
    return [
      'url' => $image['url'],
      'alt' => $image['alt'] ?: $fallback_alt,
    ];
  }

  return [
    'url' => get_theme_file_uri( 'assets/images/nosotros/' . $fallback_file ),
    'alt' => $fallback_alt,
  ];
} // end nosotros_canut_image

get_template_part( 'template-parts/nosotros/hero', '', [
  'image'   => nosotros_canut_image( get_field( 'hero_image' ), 'hero-manifesto.png', __( 'Perro descansando junto a una cama CANUT en una sala de estar', 'air-light' ) ),
  'eyebrow' => get_field( 'hero_eyebrow' ) ?: __( 'Nuestro manifiesto', 'air-light' ),
  'title'   => get_field( 'hero_title' ) ?: __( 'Por qué existe CANUT', 'air-light' ),
  'text'    => get_field( 'hero_text' ) ?: __( 'Nacimos de una obsesión silenciosa: la tranquilidad de saber, sin ninguna duda, que ellos están recibiendo lo mejor. No es solo comida; es el compromiso con su longevidad.', 'air-light' ),
] );

get_template_part( 'template-parts/nosotros/origen', '', [
  'image'   => nosotros_canut_image( get_field( 'origen_image' ), 'origen-cocina.png', __( 'Mujer preparando alimento fresco en su cocina junto a su perro', 'air-light' ) ),
  'title'   => get_field( 'origen_title' ) ?: __( 'La tranquilidad de lo bien hecho', 'air-light' ),
  'text_1'  => get_field( 'origen_text_1' ) ?: __( 'CANUT comenzó en una cocina familiar, donde la frustración por la opacidad de la industria pet-care se convirtió en curiosidad científica. Nos preguntamos: ¿Por qué el diseño y la salud animal no pueden coexistir en la misma pieza de excelencia?', 'air-light' ),
  'text_2'  => get_field( 'origen_text_2' ) ?: __( 'Creemos que alimentar a tu mascota no debería ser una tarea, sino un ritual de cuidado. Nuestra misión es eliminar la incertidumbre, ofreciendo una transparencia radical en cada ingrediente y una precisión tecnológica que garantiza que cada bocado es exactamente lo que su cuerpo necesita.', 'air-light' ),
  'quote'   => get_field( 'origen_quote' ) ?: __( 'No diseñamos productos para mascotas. Diseñamos legados de bienestar para miembros de la familia.', 'air-light' ),
] );

$valores_cards = [];
if ( have_rows( 'valores_cards' ) ) {
  while ( have_rows( 'valores_cards' ) ) {
    the_row();
    $card_image = get_sub_field( 'image' );
    $valores_cards[] = [
      'icon'       => get_sub_field( 'icon' ) ?: 'star',
      'variant'    => get_sub_field( 'variant' ) ?: 'light',
      'image'      => ! empty( $card_image['url'] ) ? [ 'url' => $card_image['url'], 'alt' => $card_image['alt'] ?: '' ] : null,
      'title'      => get_sub_field( 'title' ),
      'text'       => get_sub_field( 'text' ),
      'tags'       => get_sub_field( 'tags' ) ? array_map( 'trim', explode( ',', get_sub_field( 'tags' ) ) ) : [],
      'link_label' => get_sub_field( 'link_label' ),
      'stat_value' => get_sub_field( 'stat_value' ),
      'stat_label' => get_sub_field( 'stat_label' ),
    ];
  }
}
if ( ! $valores_cards ) {
  $valores_cards = [
    [
      'icon'    => 'pencil-ruler',
      'variant' => 'light',
      'image'   => nosotros_canut_image( null, 'diseno-atemporal.png', __( 'Comedero CANUT en madera y cerámica sobre un mueble minimalista', 'air-light' ) ),
      'title'   => __( 'Diseño Atemporal', 'air-light' ),
      'text'    => __( 'Fusionamos la estética minimalista con la funcionalidad ergonómica. Cada objeto CANUT está pensado para elevar el interiorismo de tu hogar mientras respeta la naturaleza física de tu mascota.', 'air-light' ),
      'tags'       => [],
      'link_label' => null,
      'stat_value' => null,
      'stat_label' => null,
    ],
    [
      'icon'       => 'medal',
      'variant'    => 'dark',
      'image'      => null,
      'title'      => __( 'Calidad Humana', 'air-light' ),
      'text'       => __( 'Nuestros estándares no son "pet-grade", son "human-grade". Si no es lo suficientemente bueno para nosotros, no es lo suficientemente bueno para ellos.', 'air-light' ),
      'tags'       => [],
      'link_label' => null,
      'stat_value' => '100%',
      'stat_label' => __( 'Trazabilidad', 'air-light' ),
    ],
    [
      'icon'       => 'brain',
      'variant'    => 'light',
      'image'      => null,
      'title'      => __( 'Ciencia Etológica', 'air-light' ),
      'text'       => __( 'Colaboramos con expertos en comportamiento animal para entender no solo qué comen, sino cómo interactúan con su entorno. Cada producto es una respuesta a una necesidad instintiva real.', 'air-light' ),
      'tags'       => [ __( 'Bienestar cognitivo', 'air-light' ), __( 'Postura natural', 'air-light' ) ],
      'link_label' => null,
      'stat_value' => null,
      'stat_label' => null,
    ],
    [
      'icon'       => 'whatsapp',
      'variant'    => 'accent',
      'image'      => null,
      'title'      => __( 'Conserjería Humana', 'air-light' ),
      'text'       => __( 'Nada de bots. Un equipo de expertos a un mensaje de distancia para resolver dudas nutricionales o técnicas en tiempo real.', 'air-light' ),
      'tags'       => [],
      'link_label' => __( 'Conoce a nuestro equipo', 'air-light' ),
      'stat_value' => null,
      'stat_label' => null,
    ],
  ];
}
get_template_part( 'template-parts/nosotros/valores', '', [
  'eyebrow' => get_field( 'valores_eyebrow' ) ?: __( 'Pilares fundamentales', 'air-light' ),
  'title'   => get_field( 'valores_title' ) ?: __( 'Nuestros Diferenciales', 'air-light' ),
  'cards'   => $valores_cards,
] );

get_template_part( 'template-parts/nosotros/cta', '', [
  'title'                => get_field( 'cta_title' ) ?: __( 'Únete a la excelencia CANUT', 'air-light' ),
  'text'                 => get_field( 'cta_text' ) ?: __( 'Descubre cómo podemos transformar el día a día de tu compañero con tecnología, diseño y amor honesto.', 'air-light' ),
  'cta_primary_label'    => get_field( 'cta_primary_label' ) ?: __( 'Explorar el Catálogo', 'air-light' ),
  'cta_primary_url'      => get_field( 'cta_primary_url' ) ?: home_url( '/tienda/' ),
  'cta_secondary_label'  => get_field( 'cta_secondary_label' ) ?: __( 'WhatsApp de Contacto', 'air-light' ),
  'cta_secondary_url'    => get_field( 'cta_secondary_url' ) ?: '#',
] );
