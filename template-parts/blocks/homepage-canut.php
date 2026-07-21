<?php
/**
 * Block: Página de inicio CANUT.
 *
 * One block renders the entire homepage. Every section below is fed by the
 * ACF fields registered in inc/acf-fields/homepage-canut.php - editing the
 * Front page means filling in this block's fields. Each field falls back to
 * the original CANUT design copy/imagery when left empty.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Returns an ACF image field's url/alt, falling back to a theme-bundled
 * default image when the field is empty.
 *
 * @param array|null $image ACF image field value (return_format: array).
 * @param string     $fallback_file Filename inside assets/images/homepage/.
 * @param string     $fallback_alt Alt text to use with the fallback image.
 * @return array{url: string, alt: string}
 */
function homepage_canut_image( $image, $fallback_file, $fallback_alt ) {
  if ( ! empty( $image['url'] ) ) {
    return [
      'url' => $image['url'],
      'alt' => $image['alt'] ?: $fallback_alt,
    ];
  }

  return [
    'url' => get_theme_file_uri( 'assets/images/homepage/' . $fallback_file ),
    'alt' => $fallback_alt,
  ];
} // end homepage_canut_image

get_template_part( 'template-parts/front-page/hero', '', [
  'image'      => homepage_canut_image( get_field( 'hero_image' ), 'hero-dog-feeder.jpg', __( 'Perro descansando junto a un comedero inteligente CANUT', 'air-light' ) ),
  'title'      => get_field( 'hero_title' ) ?: __( 'Tu perro siempre come a tiempo', 'air-light' ),
  'subtitle'   => get_field( 'hero_subtitle' ) ?: __( 'Tecnología y diseño artesanal creados para el bienestar de quien más te quiere.', 'air-light' ),
  'cta_label'  => get_field( 'hero_cta_label' ) ?: __( 'Comprar ahora', 'air-light' ),
  'cta_url'    => get_field( 'hero_cta_url' ) ?: '#featured-product',
] );

$trust_items = [];
if ( have_rows( 'trust_items' ) ) {
  while ( have_rows( 'trust_items' ) ) {
    the_row();
    $trust_items[] = [
      'icon'  => get_sub_field( 'icon' ) ?: 'shield-check',
      'label' => get_sub_field( 'label' ),
    ];
  }
}
if ( ! $trust_items ) {
  $trust_items = [
    [ 'icon' => 'shield-check', 'label' => __( 'Garantía de por vida', 'air-light' ) ],
    [ 'icon' => 'truck', 'label' => __( 'Envíos Medellín y Nacional', 'air-light' ) ],
    [ 'icon' => 'chat-circle', 'label' => __( 'Soporte WhatsApp 24/7', 'air-light' ) ],
    [ 'icon' => 'wrench', 'label' => __( 'Excelencia artesanal', 'air-light' ) ],
  ];
}
get_template_part( 'template-parts/front-page/trust-band', '', [ 'items' => $trust_items ] );

get_template_part( 'template-parts/front-page/lifestyle', '', [
  'image' => homepage_canut_image( get_field( 'lifestyle_image' ), 'lifestyle-living-room.jpg', __( 'Sala de estar acogedora con un comedero CANUT junto a la mesa de centro', 'air-light' ) ),
  'title' => get_field( 'lifestyle_title' ) ?: __( 'No estás en casa a la hora de comer, pero tu perro sí.', 'air-light' ),
  'text'  => get_field( 'lifestyle_text' ) ?: __( 'Programación inteligente para que nunca falte su plato.', 'air-light' ),
] );

$featured_product = null;

if ( function_exists( 'wc_get_product' ) ) {
  $featured_product_id = (int) get_field( 'featured_product' );
  $featured_product     = $featured_product_id ? wc_get_product( $featured_product_id ) : null;

  // No product picked in ACF yet - fall back to the only product the store has.
  if ( ! $featured_product instanceof \WC_Product ) {
    $default_products = wc_get_products( [
      'status' => 'publish',
      'limit'  => 1,
    ] );
    $featured_product = $default_products[0] ?? null;
  }
}

if ( $featured_product instanceof \WC_Product ) {
  $featured_product_image_id = $featured_product->get_image_id();

  get_template_part( 'template-parts/front-page/featured-product', '', [
    'image'                => $featured_product_image_id
      ? [
        'url' => wp_get_attachment_image_url( $featured_product_image_id, 'large' ),
        'alt' => get_post_meta( $featured_product_image_id, '_wp_attachment_image_alt', true ) ?: $featured_product->get_name(),
      ]
      : homepage_canut_image( null, 'featured-product.jpg', $featured_product->get_name() ),
    'eyebrow'              => get_field( 'featured_eyebrow' ) ?: __( 'Edición limitada', 'air-light' ),
    'title'                => $featured_product->get_name(),
    'description'          => $featured_product->get_short_description() ? wp_strip_all_tags( $featured_product->get_short_description() ) : null,
    'price'                => $featured_product->get_price_html(),
    'cta_primary_label'    => get_field( 'featured_cta_primary_label' ) ?: __( 'Lo quiero ya', 'air-light' ),
    'cta_primary_url'      => add_query_arg( 'add-to-cart', $featured_product->get_id(), wc_get_checkout_url() ),
    'cta_secondary_label'  => get_field( 'featured_cta_secondary_label' ) ?: __( 'Especificaciones', 'air-light' ),
    'cta_secondary_url'    => $featured_product->get_permalink(),
  ] );
} else {
  get_template_part( 'template-parts/front-page/featured-product' );
}

$how_it_works_steps = [];
if ( have_rows( 'how_it_works_steps' ) ) {
  $step_number = 1;
  while ( have_rows( 'how_it_works_steps' ) ) {
    the_row();
    $how_it_works_steps[] = [
      'image' => homepage_canut_image( get_sub_field( 'image' ), 'how-it-works-' . $step_number . '.jpg', get_sub_field( 'title' ) ),
      'title' => get_sub_field( 'title' ),
      'text'  => get_sub_field( 'text' ),
    ];
    $step_number++;
  }
}
if ( ! $how_it_works_steps ) {
  $how_it_works_steps = [
    [
      'image' => homepage_canut_image( null, 'how-it-works-1.jpg', __( 'Persona configurando la app CANUT desde su celular', 'air-light' ) ),
      'title' => __( '01. Configura', 'air-light' ),
      'text'  => __( 'Define horarios y porciones exactas desde nuestra App intuitiva.', 'air-light' ),
    ],
    [
      'image' => homepage_canut_image( null, 'how-it-works-2.jpg', __( 'Comedero CANUT dispensando alimento en un plato', 'air-light' ) ),
      'title' => __( '02. Sirve', 'air-light' ),
      'text'  => __( 'El mecanismo silencioso entrega el alimento con precisión gramatical.', 'air-light' ),
    ],
    [
      'image' => homepage_canut_image( null, 'how-it-works-3.jpg', __( 'Mujer acariciando a su perro junto al comedero CANUT', 'air-light' ) ),
      'title' => __( '03. Disfruta', 'air-light' ),
      'text'  => __( 'Tu mascota está feliz y tú tienes la tranquilidad que se merece.', 'air-light' ),
    ],
  ];
}
get_template_part( 'template-parts/front-page/how-it-works', '', [
  'title' => get_field( 'how_it_works_title' ) ?: __( 'Simplicidad en cada detalle', 'air-light' ),
  'steps' => $how_it_works_steps,
] );

get_template_part( 'template-parts/front-page/emotional-branding', '', [
  'image' => homepage_canut_image( get_field( 'emotional_image' ), 'emotional-branding.jpg', __( 'Primer plano del rostro de un golden retriever', 'air-light' ) ),
  'title' => get_field( 'emotional_title' ) ?: __( 'Vendemos tranquilidad', 'air-light' ),
] );

$featured_historias = new \WP_Query( [
  'post_type'      => 'historia',
  'posts_per_page' => 12,
  'meta_key'       => 'is_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
  'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
  'orderby'        => 'menu_order',
  'order'          => 'ASC',
] );

$historias = [];
if ( $featured_historias->have_posts() ) {
  while ( $featured_historias->have_posts() ) {
    $featured_historias->the_post();
    $historia_id = get_the_ID();
    $historias[] = [
      'image'  => homepage_canut_image( [ 'url' => get_the_post_thumbnail_url( $historia_id, 'medium' ), 'alt' => get_the_title() ], 'review-1.jpg', get_the_title() ),
      'rating' => (int) ( get_field( 'rating', $historia_id ) ?: 5 ),
      'quote'  => get_field( 'quote', $historia_id ),
      'author' => get_the_title(),
    ];
  }
  wp_reset_postdata();
}
if ( ! $historias ) {
  $historias = [
    [
      'image'  => homepage_canut_image( null, 'review-1.jpg', __( 'Camila jugando con su perro Bruno en la sala', 'air-light' ) ),
      'rating' => 5,
      'quote'  => __( 'Cambió por completo mi rutina. Viajo mucho y saber que Bruno come a sus horas me quita un peso de encima. Además, se ve hermoso en mi sala.', 'air-light' ),
      'author' => __( '— Camila & Bruno', 'air-light' ),
    ],
    [
      'image'  => homepage_canut_image( null, 'review-2.jpg', __( 'Perro negro sentado junto a su comedero CANUT en la cocina', 'air-light' ) ),
      'rating' => 5,
      'quote'  => __( 'La calidad de los materiales es impresionante. No es un juguete plástico, es un mueble de lujo que realmente funciona.', 'air-light' ),
      'author' => __( '— Javier G.', 'air-light' ),
    ],
    [
      'image'  => homepage_canut_image( null, 'review-3.jpg', __( 'Perro junto a su comedero CANUT y su dueña de fondo', 'air-light' ) ),
      'rating' => 5,
      'quote'  => __( 'Excelente servicio al cliente. Me ayudaron con la configuración por WhatsApp en minutos. 10/10.', 'air-light' ),
      'author' => __( '— Maria P.', 'air-light' ),
    ],
  ];
}

get_template_part( 'template-parts/front-page/historias', '', [
  'title'       => get_field( 'reviews_title' ) ?: __( 'Historias CANUT', 'air-light' ),
  'historias'   => $historias,
  'archive_url' => get_post_type_archive_link( 'historia' ) ?: '',
] );

get_template_part( 'template-parts/front-page/warranty-band', '', [
  'title'     => get_field( 'warranty_title' ) ?: __( 'Compromiso CANUT: 2 años de garantía total', 'air-light' ),
  'text'      => get_field( 'warranty_text' ) ?: __( 'Diseñados para durar toda una vida perruna.', 'air-light' ),
  'cta_label' => get_field( 'warranty_cta_label' ) ?: __( 'Más información', 'air-light' ),
  'cta_url'   => get_field( 'warranty_cta_url' ) ?: '#',
] );

get_template_part( 'template-parts/front-page/final-cta', '', [
  'image'     => homepage_canut_image( get_field( 'final_cta_image' ), 'final-cta.jpg', __( 'Línea completa de productos CANUT sobre un mueble de madera', 'air-light' ) ),
  'title'     => get_field( 'final_cta_title' ) ?: __( 'El futuro del cuidado está aquí', 'air-light' ),
  'cta_label' => get_field( 'final_cta_label' ) ?: __( 'Ver comederos', 'air-light' ),
  'cta_url'   => get_field( 'final_cta_url' ) ?: home_url( '/tienda/' ),
] );
