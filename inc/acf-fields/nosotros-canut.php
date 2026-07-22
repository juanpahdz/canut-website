<?php
/**
 * Field group for the "Página Nosotros CANUT" ACF block
 * (template-parts/blocks/nosotros-canut.php).
 *
 * One block holds every "Nosotros" section so editing the page means
 * filling in this single block's fields, tab by tab.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

$icon_choices = [
  'pencil-ruler' => __( 'Regla y lápiz (diseño)', 'air-light' ),
  'medal'        => __( 'Medalla (calidad)', 'air-light' ),
  'brain'        => __( 'Cerebro (ciencia)', 'air-light' ),
  'whatsapp'     => __( 'WhatsApp (servicio)', 'air-light' ),
  'shield-check' => __( 'Escudo (garantía)', 'air-light' ),
  'chat-circle'  => __( 'Chat (soporte)', 'air-light' ),
  'wrench'       => __( 'Herramienta (artesanal)', 'air-light' ),
  'star'         => __( 'Estrella', 'air-light' ),
];

$variant_choices = [
  'light'  => __( 'Claro (fondo blanco)', 'air-light' ),
  'dark'   => __( 'Oscuro (fondo verde)', 'air-light' ),
  'accent' => __( 'Acento (fondo naranja)', 'air-light' ),
];

acf_add_local_field_group( [
  'key'    => 'group_nosotros_canut',
  'title'  => __( 'Página Nosotros CANUT', 'air-light' ),
  'fields' => [

    // Hero
    [
      'key'   => 'field_npc_tab_hero',
      'label' => __( '1. Hero', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_npc_hero_image',
      'label'         => __( 'Imagen de fondo', 'air-light' ),
      'name'          => 'hero_image',
      'type'          => 'image',
      'return_format' => 'array',
      'preview_size'  => 'medium',
      'instructions'  => __( 'Se usa sola si no hay video, y como poster (fotograma inicial) mientras el video carga.', 'air-light' ),
    ],
    [
      'key'           => 'field_npc_hero_video',
      'label'         => __( 'Video de fondo (opcional)', 'air-light' ),
      'name'          => 'hero_video',
      'type'          => 'file',
      'return_format' => 'array',
      'mime_types'    => 'mp4,webm,mov',
      'instructions'  => __( 'Si se sube, reemplaza la imagen de fondo: se reproduce en loop automático, sin sonido y sin controles. Deja vacío para usar solo la imagen.', 'air-light' ),
    ],
    [
      'key'   => 'field_npc_hero_eyebrow',
      'label' => __( 'Etiqueta superior', 'air-light' ),
      'name'  => 'hero_eyebrow',
      'type'  => 'text',
      'default_value' => 'Nuestro manifiesto',
    ],
    [
      'key'   => 'field_npc_hero_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'hero_title',
      'type'  => 'text',
      'default_value' => 'Por qué existe CANUT',
    ],
    [
      'key'   => 'field_npc_hero_text',
      'label' => __( 'Texto', 'air-light' ),
      'name'  => 'hero_text',
      'type'  => 'textarea',
      'rows'  => 3,
      'default_value' => 'Nacimos de una obsesión silenciosa: la tranquilidad de saber, sin ninguna duda, que ellos están recibiendo lo mejor. No es solo comida; es el compromiso con su longevidad.',
    ],

    // Origen
    [
      'key'   => 'field_npc_tab_origen',
      'label' => __( '2. Origen', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_npc_origen_image',
      'label'         => __( 'Imagen', 'air-light' ),
      'name'          => 'origen_image',
      'type'          => 'image',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ],
    [
      'key'   => 'field_npc_origen_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'origen_title',
      'type'  => 'text',
      'default_value' => 'La tranquilidad de lo bien hecho',
    ],
    [
      'key'   => 'field_npc_origen_text_1',
      'label' => __( 'Párrafo 1', 'air-light' ),
      'name'  => 'origen_text_1',
      'type'  => 'textarea',
      'rows'  => 3,
      'default_value' => 'CANUT comenzó en una cocina familiar, donde la frustración por la opacidad de la industria pet-care se convirtió en curiosidad científica. Nos preguntamos: ¿Por qué el diseño y la salud animal no pueden coexistir en la misma pieza de excelencia?',
    ],
    [
      'key'   => 'field_npc_origen_text_2',
      'label' => __( 'Párrafo 2', 'air-light' ),
      'name'  => 'origen_text_2',
      'type'  => 'textarea',
      'rows'  => 3,
      'default_value' => 'Creemos que alimentar a tu mascota no debería ser una tarea, sino un ritual de cuidado. Nuestra misión es eliminar la incertidumbre, ofreciendo una transparencia radical en cada ingrediente y una precisión tecnológica que garantiza que cada bocado es exactamente lo que su cuerpo necesita.',
    ],
    [
      'key'   => 'field_npc_origen_quote',
      'label' => __( 'Cita destacada', 'air-light' ),
      'name'  => 'origen_quote',
      'type'  => 'textarea',
      'rows'  => 2,
      'default_value' => 'No diseñamos productos para mascotas. Diseñamos legados de bienestar para miembros de la familia.',
    ],

    // Valores / Bento grid
    [
      'key'   => 'field_npc_tab_valores',
      'label' => __( '3. Diferenciales', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_npc_valores_eyebrow',
      'label' => __( 'Etiqueta superior', 'air-light' ),
      'name'  => 'valores_eyebrow',
      'type'  => 'text',
      'default_value' => 'Pilares fundamentales',
    ],
    [
      'key'   => 'field_npc_valores_title',
      'label' => __( 'Título de la sección', 'air-light' ),
      'name'  => 'valores_title',
      'type'  => 'text',
      'default_value' => 'Nuestros Diferenciales',
    ],
    [
      'key'          => 'field_npc_valores_cards',
      'label'        => __( 'Tarjetas', 'air-light' ),
      'name'         => 'valores_cards',
      'type'         => 'repeater',
      'min'          => 4,
      'max'          => 4,
      'layout'       => 'block',
      'button_label' => __( 'Agregar tarjeta', 'air-light' ),
      'instructions' => __( 'Las 4 tarjetas mantienen el orden y la posición del diseño (bento grid): 1. grande, 2. oscura con dato, 3. con etiquetas, 4. acento con enlace.', 'air-light' ),
      'sub_fields'   => [
        [
          'key'     => 'field_npc_card_icon',
          'label'   => __( 'Ícono', 'air-light' ),
          'name'    => 'icon',
          'type'    => 'select',
          'choices' => $icon_choices,
          'wrapper' => [ 'width' => '33' ],
        ],
        [
          'key'           => 'field_npc_card_variant',
          'label'         => __( 'Estilo de fondo', 'air-light' ),
          'name'          => 'variant',
          'type'          => 'select',
          'choices'       => $variant_choices,
          'wrapper'       => [ 'width' => '33' ],
        ],
        [
          'key'          => 'field_npc_card_image',
          'label'        => __( 'Imagen (opcional)', 'air-light' ),
          'name'         => 'image',
          'type'         => 'image',
          'return_format' => 'array',
          'preview_size' => 'medium',
          'wrapper'      => [ 'width' => '34' ],
          'instructions' => __( 'Solo se usa en la primera tarjeta.', 'air-light' ),
        ],
        [
          'key'   => 'field_npc_card_title',
          'label' => __( 'Título', 'air-light' ),
          'name'  => 'title',
          'type'  => 'text',
        ],
        [
          'key'   => 'field_npc_card_text',
          'label' => __( 'Texto', 'air-light' ),
          'name'  => 'text',
          'type'  => 'textarea',
          'rows'  => 3,
        ],
        [
          'key'          => 'field_npc_card_tags',
          'label'        => __( 'Etiquetas (opcional)', 'air-light' ),
          'name'         => 'tags',
          'type'         => 'text',
          'instructions' => __( 'Separadas por coma. Solo se usan en la tercera tarjeta.', 'air-light' ),
          'wrapper'      => [ 'width' => '50' ],
        ],
        [
          'key'          => 'field_npc_card_link_label',
          'label'        => __( 'Texto del enlace (opcional)', 'air-light' ),
          'name'         => 'link_label',
          'type'         => 'text',
          'instructions' => __( 'Solo se usa en la cuarta tarjeta.', 'air-light' ),
          'wrapper'      => [ 'width' => '50' ],
        ],
        [
          'key'     => 'field_npc_card_stat_value',
          'label'   => __( 'Dato destacado (opcional)', 'air-light' ),
          'name'    => 'stat_value',
          'type'    => 'text',
          'instructions' => __( 'Ej. "100%". Solo se usa en la segunda tarjeta.', 'air-light' ),
          'wrapper' => [ 'width' => '50' ],
        ],
        [
          'key'     => 'field_npc_card_stat_label',
          'label'   => __( 'Etiqueta del dato (opcional)', 'air-light' ),
          'name'    => 'stat_label',
          'type'    => 'text',
          'instructions' => __( 'Ej. "Trazabilidad".', 'air-light' ),
          'wrapper' => [ 'width' => '50' ],
        ],
      ],
    ],

    // Final CTA
    [
      'key'   => 'field_npc_tab_cta',
      'label' => __( '4. CTA final', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_npc_cta_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'cta_title',
      'type'  => 'text',
      'default_value' => 'Únete a la excelencia CANUT',
    ],
    [
      'key'   => 'field_npc_cta_text',
      'label' => __( 'Texto', 'air-light' ),
      'name'  => 'cta_text',
      'type'  => 'textarea',
      'rows'  => 2,
      'default_value' => 'Descubre cómo podemos transformar el día a día de tu compañero con tecnología, diseño y amor honesto.',
    ],
    [
      'key'   => 'field_npc_cta_primary_label',
      'label' => __( 'Botón primario: texto', 'air-light' ),
      'name'  => 'cta_primary_label',
      'type'  => 'text',
      'default_value' => 'Explorar el Catálogo',
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_npc_cta_primary_url',
      'label' => __( 'Botón primario: enlace', 'air-light' ),
      'name'  => 'cta_primary_url',
      'type'  => 'url',
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_npc_cta_secondary_label',
      'label' => __( 'Botón secundario: texto', 'air-light' ),
      'name'  => 'cta_secondary_label',
      'type'  => 'text',
      'default_value' => 'WhatsApp de Contacto',
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_npc_cta_secondary_url',
      'label' => __( 'Botón secundario: enlace', 'air-light' ),
      'name'  => 'cta_secondary_url',
      'type'  => 'url',
      'wrapper' => [ 'width' => '50' ],
    ],

  ],
  'location' => [
    [
      [
        'param'    => 'block',
        'operator' => '==',
        'value'    => 'acf/nosotros-canut',
      ],
    ],
  ],
] );
