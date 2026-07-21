<?php
/**
 * Block: Página Garantía CANUT.
 *
 * One block renders the entire "Garantía" page. Every section below is fed
 * by the ACF fields registered in inc/acf-fields/garantia-canut.php -
 * editing the Garantía page means filling in this block's fields. Each
 * field/repeater falls back to the original CANUT design copy when empty.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Returns an ACF image field's url/alt, falling back to a theme-bundled
 * default image when the field is empty.
 *
 * @param array|null $image ACF image field value (return_format: array).
 * @return array{url: string, alt: string}
 */
function garantia_canut_image( $image ) {
  if ( ! empty( $image['url'] ) ) {
    return [
      'url' => $image['url'],
      'alt' => $image['alt'] ?: __( 'Comedero CANUT', 'air-light' ),
    ];
  }

  return [
    'url' => get_theme_file_uri( 'assets/images/homepage/hero-dog-feeder.jpg' ),
    'alt' => __( 'Comedero CANUT en un ambiente doméstico', 'air-light' ),
  ];
} // end garantia_canut_image

get_template_part( 'template-parts/garantia/hero', '', [
  'badge_text'      => get_field( 'hero_badge_text' ) ?: __( 'Respaldo Total', 'air-light' ),
  'title'           => get_field( 'hero_title' ) ?: __( 'Garantía', 'air-light' ),
  'text'            => get_field( 'hero_text' ) ?: __( 'Tu comedero CANUT está respaldado por nuestra promesa de excelencia y durabilidad. Diseñamos para la eternidad, cuidando cada detalle para el bienestar de tu mascota.', 'air-light' ),
  'image'           => garantia_canut_image( get_field( 'hero_image' ) ),
  'highlight_title' => get_field( 'hero_highlight_title' ) ?: __( 'Compromiso Canut', 'air-light' ),
  'highlight_text'  => get_field( 'hero_highlight_text' ) ?: __( 'Artesanía de alto rendimiento garantizada.', 'air-light' ),
] );

$article_sections = [];
if ( have_rows( 'article_sections' ) ) {
  while ( have_rows( 'article_sections' ) ) {
    the_row();
    $list_items = [];
    if ( have_rows( 'list_items' ) ) {
      while ( have_rows( 'list_items' ) ) {
        the_row();
        $item_text = get_sub_field( 'text' );
        if ( $item_text ) {
          $list_items[] = $item_text;
        }
      }
    }
    $article_sections[] = [
      'icon'      => get_sub_field( 'icon' ) ?: 'shield-check',
      'title'     => get_sub_field( 'title' ),
      'content'   => get_sub_field( 'content' ),
      'highlight' => (bool) get_sub_field( 'highlight' ),
      'list_items' => $list_items,
    ];
  }
}
if ( ! $article_sections ) {
  $article_sections = [
    [
      'icon'      => 'shield-check',
      'title'     => __( 'Garantía Legal', 'air-light' ),
      'content'   => '<p>' . __( 'En cumplimiento con el <strong>Estatuto del Consumidor (Ley 1480 de 2011)</strong>, todos nuestros productos cuentan con una garantía que cubre defectos de fabricación y calidad. CANUT asegura que los materiales y la funcionalidad del comedero cumplen con los más altos estándares de la industria pet-tech.', 'air-light' ) . '</p>',
      'highlight' => false,
      'list_items' => [],
    ],
    [
      'icon'      => 'arrow-counter-clockwise',
      'title'     => __( 'Derecho de Retracto', 'air-light' ),
      'content'   => '<p>' . __( 'De acuerdo con la normativa vigente, el cliente tiene derecho a retractarse de su compra dentro de los <strong>cinco (5) días hábiles</strong> siguientes a la entrega del producto.', 'air-light' ) . '</p>',
      'highlight' => true,
      'list_items' => [
        __( 'El producto debe estar sin uso y en su empaque original.', 'air-light' ),
        __( 'Los costos de transporte para la devolución serán asumidos por el consumidor.', 'air-light' ),
      ],
    ],
    [
      'icon'      => 'truck',
      'title'     => __( 'Avería en Transporte', 'air-light' ),
      'content'   => '<p>' . __( 'Si tu pedido llega con cualquier tipo de daño físico o avería causada por el transporte, realizaremos un <strong>cambio inmediato</strong> sin costo adicional para ti. Reporta la novedad dentro de las primeras 24 horas tras la recepción.', 'air-light' ) . '</p>',
      'highlight' => false,
      'list_items' => [],
    ],
  ];
}
get_template_part( 'template-parts/garantia/article', '', [
  'sections' => $article_sections,
] );

$exclusions_items = [];
if ( have_rows( 'exclusions_items' ) ) {
  while ( have_rows( 'exclusions_items' ) ) {
    the_row();
    $exclusions_items[] = [
      'title' => get_sub_field( 'title' ),
      'text'  => get_sub_field( 'text' ),
    ];
  }
}
if ( ! $exclusions_items ) {
  $exclusions_items = [
    [
      'title' => __( 'Uso Inadecuado', 'air-light' ),
      'text'  => __( 'Daños causados por accidentes o maltrato físico del producto.', 'air-light' ),
    ],
    [
      'title' => __( 'Desgaste Natural', 'air-light' ),
      'text'  => __( 'Cambios normales en materiales tras años de uso continuado.', 'air-light' ),
    ],
    [
      'title' => __( 'Terceros', 'air-light' ),
      'text'  => __( 'Cualquier intervención o reparación no autorizada por CANUT.', 'air-light' ),
    ],
    [
      'title' => __( 'Productos Químicos', 'air-light' ),
      'text'  => __( 'Limpieza con agentes abrasivos que dañen los acabados premium.', 'air-light' ),
    ],
  ];
}
get_template_part( 'template-parts/garantia/exclusions', '', [
  'title' => get_field( 'exclusions_title' ) ?: __( 'Exclusiones de Garantía', 'air-light' ),
  'text'  => get_field( 'exclusions_text' ) ?: __( 'Nuestra promesa de excelencia es sólida, pero existen condiciones que invalidan la garantía para proteger la integridad de nuestros procesos artesanales:', 'air-light' ),
  'items' => $exclusions_items,
] );

$trust_signals = [];
if ( have_rows( 'trust_signals' ) ) {
  while ( have_rows( 'trust_signals' ) ) {
    the_row();
    $trust_signals[] = [
      'icon'    => get_sub_field( 'icon' ) ?: 'medal',
      'variant' => get_sub_field( 'variant' ) ?: 'light',
      'title'   => get_sub_field( 'title' ),
      'text'    => get_sub_field( 'text' ),
    ];
  }
}
if ( ! $trust_signals ) {
  $trust_signals = [
    [
      'icon'    => 'medal',
      'variant' => 'light',
      'title'   => __( 'Calidad Certificada', 'air-light' ),
      'text'    => __( 'Cada unidad es inspeccionada manualmente antes de salir de nuestro taller.', 'air-light' ),
    ],
    [
      'icon'    => 'headset',
      'variant' => 'dark',
      'title'   => __( 'Soporte Concierge', 'air-light' ),
      'text'    => __( 'Atención personalizada para resolver cualquier duda en tiempo récord.', 'air-light' ),
    ],
    [
      'icon'    => 'paw-print',
      'variant' => 'light',
      'title'   => __( 'Legado de Durabilidad', 'air-light' ),
      'text'    => __( 'Nuestros productos están diseñados para acompañar a tu mascota toda la vida.', 'air-light' ),
    ],
  ];
}

$cta_whatsapp_number = get_field( 'cta_whatsapp_number' );
$cta_whatsapp_url    = $cta_whatsapp_number
  ? 'https://wa.me/' . preg_replace( '/\D/', '', $cta_whatsapp_number )
  : 'https://wa.me/';

get_template_part( 'template-parts/garantia/trust-cta', '', [
  'signals'      => $trust_signals,
  'cta_title'    => get_field( 'cta_title' ) ?: __( '¿Tienes dudas sobre tu proceso?', 'air-light' ),
  'cta_text'     => get_field( 'cta_text' ) ?: __( 'Nuestro equipo de soporte está listo para ayudarte vía WhatsApp.', 'air-light' ),
  'cta_button_label' => get_field( 'cta_button_label' ) ?: __( 'Escríbenos por WhatsApp', 'air-light' ),
  'cta_button_url'   => $cta_whatsapp_url,
] );
