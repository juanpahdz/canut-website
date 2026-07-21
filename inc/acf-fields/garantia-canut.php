<?php
/**
 * Field group for the "Página Garantía CANUT" ACF block
 * (template-parts/blocks/garantia-canut.php).
 *
 * One block holds every "Garantía" section (hero, artículo legal,
 * exclusiones, confianza + CTA) so editing the page means filling in this
 * single block's fields, tab by tab. Repeaters (article_sections,
 * exclusions_items, trust_signals, and the nested list_items) are how
 * editors add/remove/reorder entries without touching code.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

$garantia_icon_choices = [
  'shield-check'          => __( 'Escudo (garantía)', 'air-light' ),
  'arrow-counter-clockwise' => __( 'Flecha circular (retracto)', 'air-light' ),
  'truck'                 => __( 'Camión (envío)', 'air-light' ),
  'wrench'                => __( 'Llave (servicio técnico)', 'air-light' ),
  'medal'                 => __( 'Medalla (calidad)', 'air-light' ),
  'headset'               => __( 'Auriculares (soporte)', 'air-light' ),
  'paw-print'             => __( 'Huella (mascota)', 'air-light' ),
  'x'                     => __( 'X (exclusión)', 'air-light' ),
  'check'                 => __( 'Check', 'air-light' ),
];

acf_add_local_field_group( [
  'key'    => 'group_garantia_canut',
  'title'  => __( 'Página Garantía CANUT', 'air-light' ),
  'fields' => [

    // Hero
    [
      'key'   => 'field_gc_tab_hero',
      'label' => __( '1. Hero', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_gc_hero_badge_text',
      'label' => __( 'Texto de la etiqueta', 'air-light' ),
      'name'  => 'hero_badge_text',
      'type'  => 'text',
      'default_value' => 'Respaldo Total',
    ],
    [
      'key'   => 'field_gc_hero_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'hero_title',
      'type'  => 'text',
      'default_value' => 'Garantía',
    ],
    [
      'key'   => 'field_gc_hero_text',
      'label' => __( 'Texto', 'air-light' ),
      'name'  => 'hero_text',
      'type'  => 'textarea',
      'rows'  => 3,
      'default_value' => 'Tu comedero CANUT está respaldado por nuestra promesa de excelencia y durabilidad. Diseñamos para la eternidad, cuidando cada detalle para el bienestar de tu mascota.',
    ],
    [
      'key'           => 'field_gc_hero_image',
      'label'         => __( 'Imagen de fondo', 'air-light' ),
      'name'          => 'hero_image',
      'type'          => 'image',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ],
    [
      'key'   => 'field_gc_hero_highlight_title',
      'label' => __( 'Recuadro: título', 'air-light' ),
      'name'  => 'hero_highlight_title',
      'type'  => 'text',
      'default_value' => 'Compromiso Canut',
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_gc_hero_highlight_text',
      'label' => __( 'Recuadro: texto', 'air-light' ),
      'name'  => 'hero_highlight_text',
      'type'  => 'text',
      'default_value' => 'Artesanía de alto rendimiento garantizada.',
      'wrapper' => [ 'width' => '50' ],
    ],

    // Artículo legal
    [
      'key'   => 'field_gc_tab_article',
      'label' => __( '2. Artículo legal', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_gc_article_sections',
      'label'        => __( 'Secciones', 'air-light' ),
      'name'         => 'article_sections',
      'type'         => 'repeater',
      'min'          => 1,
      'layout'       => 'block',
      'button_label' => __( 'Agregar sección', 'air-light' ),
      'instructions' => __( 'Cada sección aparece con su propio ícono y título, en el orden en que las agregues aquí.', 'air-light' ),
      'sub_fields'   => [
        [
          'key'     => 'field_gc_section_icon',
          'label'   => __( 'Ícono', 'air-light' ),
          'name'    => 'icon',
          'type'    => 'select',
          'choices' => $garantia_icon_choices,
          'wrapper' => [ 'width' => '25' ],
        ],
        [
          'key'     => 'field_gc_section_title',
          'label'   => __( 'Título', 'air-light' ),
          'name'    => 'title',
          'type'    => 'text',
          'required' => 1,
          'wrapper' => [ 'width' => '75' ],
        ],
        [
          'key'          => 'field_gc_section_content',
          'label'        => __( 'Contenido', 'air-light' ),
          'name'         => 'content',
          'type'         => 'wysiwyg',
          'tabs'         => 'visual',
          'toolbar'      => 'Small',
          'media_upload' => 0,
          'delay'        => 1,
        ],
        [
          'key'          => 'field_gc_section_highlight',
          'label'        => __( 'Destacar', 'air-light' ),
          'name'         => 'highlight',
          'type'         => 'true_false',
          'ui'           => 1,
          'instructions' => __( 'Resalta la sección con fondo y borde de color (ej. "Derecho de Retracto").', 'air-light' ),
        ],
        [
          'key'          => 'field_gc_section_list_items',
          'label'        => __( 'Puntos clave', 'air-light' ),
          'name'         => 'list_items',
          'type'         => 'repeater',
          'layout'       => 'table',
          'button_label' => __( 'Agregar punto', 'air-light' ),
          'instructions' => __( 'Opcional. Se muestra como una lista con check debajo del contenido.', 'air-light' ),
          'sub_fields'   => [
            [
              'key'  => 'field_gc_list_item_text',
              'label' => __( 'Texto', 'air-light' ),
              'name' => 'text',
              'type' => 'text',
            ],
          ],
        ],
      ],
    ],

    // Exclusiones
    [
      'key'   => 'field_gc_tab_exclusions',
      'label' => __( '3. Exclusiones', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_gc_exclusions_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'exclusions_title',
      'type'  => 'text',
      'default_value' => 'Exclusiones de Garantía',
    ],
    [
      'key'   => 'field_gc_exclusions_text',
      'label' => __( 'Texto', 'air-light' ),
      'name'  => 'exclusions_text',
      'type'  => 'textarea',
      'rows'  => 2,
      'default_value' => 'Nuestra promesa de excelencia es sólida, pero existen condiciones que invalidan la garantía para proteger la integridad de nuestros procesos artesanales:',
    ],
    [
      'key'          => 'field_gc_exclusions_items',
      'label'        => __( 'Condiciones', 'air-light' ),
      'name'         => 'exclusions_items',
      'type'         => 'repeater',
      'min'          => 1,
      'layout'       => 'block',
      'button_label' => __( 'Agregar condición', 'air-light' ),
      'sub_fields'   => [
        [
          'key'      => 'field_gc_exclusion_title',
          'label'    => __( 'Título', 'air-light' ),
          'name'     => 'title',
          'type'     => 'text',
          'required' => 1,
          'wrapper'  => [ 'width' => '30' ],
        ],
        [
          'key'     => 'field_gc_exclusion_text',
          'label'   => __( 'Texto', 'air-light' ),
          'name'    => 'text',
          'type'    => 'textarea',
          'rows'    => 2,
          'wrapper' => [ 'width' => '70' ],
        ],
      ],
    ],

    // Confianza y CTA
    [
      'key'   => 'field_gc_tab_trust',
      'label' => __( '4. Confianza y CTA', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_gc_trust_signals',
      'label'        => __( 'Tarjetas de confianza', 'air-light' ),
      'name'         => 'trust_signals',
      'type'         => 'repeater',
      'min'          => 1,
      'max'          => 3,
      'layout'       => 'block',
      'button_label' => __( 'Agregar tarjeta', 'air-light' ),
      'sub_fields'   => [
        [
          'key'     => 'field_gc_trust_icon',
          'label'   => __( 'Ícono', 'air-light' ),
          'name'    => 'icon',
          'type'    => 'select',
          'choices' => $garantia_icon_choices,
          'wrapper' => [ 'width' => '25' ],
        ],
        [
          'key'     => 'field_gc_trust_variant',
          'label'   => __( 'Estilo', 'air-light' ),
          'name'    => 'variant',
          'type'    => 'select',
          'choices' => [
            'light' => __( 'Claro', 'air-light' ),
            'dark'  => __( 'Oscuro', 'air-light' ),
          ],
          'default_value' => 'light',
          'wrapper' => [ 'width' => '25' ],
        ],
        [
          'key'      => 'field_gc_trust_title',
          'label'    => __( 'Título', 'air-light' ),
          'name'     => 'title',
          'type'     => 'text',
          'required' => 1,
          'wrapper'  => [ 'width' => '50' ],
        ],
        [
          'key'  => 'field_gc_trust_text',
          'label' => __( 'Texto', 'air-light' ),
          'name' => 'text',
          'type' => 'textarea',
          'rows' => 2,
        ],
      ],
    ],
    [
      'key'   => 'field_gc_cta_title',
      'label' => __( 'CTA: título', 'air-light' ),
      'name'  => 'cta_title',
      'type'  => 'text',
      'default_value' => '¿Tienes dudas sobre tu proceso?',
    ],
    [
      'key'   => 'field_gc_cta_text',
      'label' => __( 'CTA: texto', 'air-light' ),
      'name'  => 'cta_text',
      'type'  => 'text',
      'default_value' => 'Nuestro equipo de soporte está listo para ayudarte vía WhatsApp.',
    ],
    [
      'key'   => 'field_gc_cta_button_label',
      'label' => __( 'CTA: texto del botón', 'air-light' ),
      'name'  => 'cta_button_label',
      'type'  => 'text',
      'default_value' => 'Escríbenos por WhatsApp',
    ],

  ],
  'location' => [
    [
      [
        'param'    => 'block',
        'operator' => '==',
        'value'    => 'acf/garantia-canut',
      ],
    ],
  ],
] );
