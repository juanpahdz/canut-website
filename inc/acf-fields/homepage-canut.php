<?php
/**
 * Field group for the "Página de inicio CANUT" ACF block
 * (template-parts/blocks/homepage-canut.php).
 *
 * One block holds every homepage section so editing the Front page means
 * filling in this single block's fields, tab by tab.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

$icon_choices = [
  'shield-check' => __( 'Escudo (garantía)', 'air-light' ),
  'truck'        => __( 'Camión (envíos)', 'air-light' ),
  'chat-circle'  => __( 'Chat (soporte)', 'air-light' ),
  'wrench'       => __( 'Herramienta (artesanal)', 'air-light' ),
  'star'         => __( 'Estrella', 'air-light' ),
  'shopping-bag' => __( 'Bolsa de compras', 'air-light' ),
  'user'         => __( 'Usuario', 'air-light' ),
  'lock'         => __( 'Candado', 'air-light' ),
  'check'        => __( 'Check', 'air-light' ),
  'camera'       => __( 'Cámara', 'air-light' ),
  'wifi-high'    => __( 'WiFi', 'air-light' ),
  'scales'       => __( 'Báscula (porciones)', 'air-light' ),
  'battery-full' => __( 'Batería', 'air-light' ),
  'drop'         => __( 'Gota (agua)', 'air-light' ),
  'package'      => __( 'Paquete (capacidad)', 'air-light' ),
  'cube'         => __( 'Cubo (material)', 'air-light' ),
  'plug'         => __( 'Enchufe', 'air-light' ),
  'paw-print'    => __( 'Huella', 'air-light' ),
  'medal'        => __( 'Medalla', 'air-light' ),
  'brain'        => __( 'Cerebro (IA)', 'air-light' ),
];

acf_add_local_field_group( [
  'key'      => 'group_homepage_canut',
  'title'    => __( 'Página de inicio CANUT', 'air-light' ),
  'fields'   => [

    // Hero
    [
      'key'   => 'field_hpc_tab_hero',
      'label' => __( '1. Hero', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'            => 'field_hpc_hero_image',
      'label'          => __( 'Imagen de fondo', 'air-light' ),
      'name'           => 'hero_image',
      'type'           => 'image',
      'return_format'  => 'array',
      'preview_size'   => 'medium',
      'instructions'   => __( 'Se usa sola si no hay video, y como poster (fotograma inicial) mientras el video carga.', 'air-light' ),
    ],
    [
      'key'            => 'field_hpc_hero_video',
      'label'          => __( 'Video de fondo (opcional)', 'air-light' ),
      'name'           => 'hero_video',
      'type'           => 'file',
      'return_format'  => 'array',
      'mime_types'     => 'mp4,webm,mov',
      'instructions'   => __( 'Si se sube, reemplaza la imagen de fondo: se reproduce en loop automático, sin sonido y sin controles. Deja vacío para usar solo la imagen.', 'air-light' ),
    ],
    [
      'key'   => 'field_hpc_hero_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'hero_title',
      'type'  => 'text',
      'default_value' => 'Tu perro siempre come a tiempo',
    ],
    [
      'key'   => 'field_hpc_hero_subtitle',
      'label' => __( 'Subtítulo', 'air-light' ),
      'name'  => 'hero_subtitle',
      'type'  => 'textarea',
      'rows'  => 2,
      'default_value' => 'Tecnología y diseño artesanal creados para el bienestar de quien más te quiere.',
    ],
    [
      'key'   => 'field_hpc_hero_cta_label',
      'label' => __( 'Texto del botón', 'air-light' ),
      'name'  => 'hero_cta_label',
      'type'  => 'text',
      'default_value' => 'Comprar ahora',
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_hpc_hero_cta_url',
      'label' => __( 'Enlace del botón', 'air-light' ),
      'name'  => 'hero_cta_url',
      'type'  => 'url',
      'default_value' => '#featured-product',
      'wrapper' => [ 'width' => '50' ],
    ],

    // Trust band
    [
      'key'   => 'field_hpc_tab_trust',
      'label' => __( '2. Banda de confianza', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_hpc_trust_items',
      'label'        => __( 'Elementos', 'air-light' ),
      'name'         => 'trust_items',
      'type'         => 'repeater',
      'min'          => 1,
      'max'          => 6,
      'layout'       => 'table',
      'button_label' => __( 'Agregar elemento', 'air-light' ),
      'sub_fields'   => [
        [
          'key'     => 'field_hpc_trust_item_icon',
          'label'   => __( 'Ícono', 'air-light' ),
          'name'    => 'icon',
          'type'    => 'select',
          'choices' => $icon_choices,
        ],
        [
          'key'   => 'field_hpc_trust_item_label',
          'label' => __( 'Texto', 'air-light' ),
          'name'  => 'label',
          'type'  => 'text',
        ],
      ],
    ],

    // Lifestyle
    [
      'key'   => 'field_hpc_tab_lifestyle',
      'label' => __( '3. Estilo de vida', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_hpc_lifestyle_image',
      'label'        => __( 'Imagen', 'air-light' ),
      'name'         => 'lifestyle_image',
      'type'         => 'image',
      'return_format' => 'array',
      'preview_size' => 'medium',
    ],
    [
      'key'   => 'field_hpc_lifestyle_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'lifestyle_title',
      'type'  => 'textarea',
      'rows'  => 2,
      'default_value' => 'No estás en casa a la hora de comer, pero tu perro sí.',
    ],
    [
      'key'   => 'field_hpc_lifestyle_text',
      'label' => __( 'Texto', 'air-light' ),
      'name'  => 'lifestyle_text',
      'type'  => 'textarea',
      'rows'  => 2,
      'default_value' => 'Programación inteligente para que nunca falte su plato.',
    ],

    // Featured product
    [
      'key'   => 'field_hpc_tab_featured',
      'label' => __( '4. Producto destacado', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_hpc_featured_product',
      'label'         => __( 'Producto', 'air-light' ),
      'name'          => 'featured_product',
      'type'          => 'post_object',
      'post_type'     => [ 'product' ],
      'return_format' => 'id',
      'ui'            => 1,
      'allow_null'    => 1,
      'instructions'  => __( 'Imagen, título, descripción y precio se toman del producto seleccionado. Si se deja vacío, se usa el único producto publicado en la tienda.', 'air-light' ),
    ],
    [
      'key'   => 'field_hpc_featured_eyebrow',
      'label' => __( 'Etiqueta superior', 'air-light' ),
      'name'  => 'featured_eyebrow',
      'type'  => 'text',
      'default_value' => 'Edición limitada',
    ],
    [
      'key'   => 'field_hpc_featured_cta_primary_label',
      'label' => __( 'Botón primario: texto', 'air-light' ),
      'name'  => 'featured_cta_primary_label',
      'type'  => 'text',
      'default_value' => 'Comprar ahora',
      'instructions' => __( 'Agrega el producto al carrito y abre el carrito lateral, sin salir de la página. Si el producto ya está en el carrito, el botón muestra "Ver carrito" y solo lo abre.', 'air-light' ),
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_hpc_featured_cta_secondary_label',
      'label' => __( 'Botón secundario: texto', 'air-light' ),
      'name'  => 'featured_cta_secondary_label',
      'type'  => 'text',
      'default_value' => 'Especificaciones',
      'instructions' => __( 'El botón enlaza a la página del producto.', 'air-light' ),
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'          => 'field_hpc_featured_specs',
      'label'        => __( 'Especificaciones', 'air-light' ),
      'name'         => 'featured_specs',
      'type'         => 'repeater',
      'min'          => 0,
      'max'          => 4,
      'layout'       => 'table',
      'button_label' => __( 'Agregar especificación', 'air-light' ),
      'instructions' => __( 'Hasta 4 especificaciones con ícono, mostradas junto a la descripción del producto.', 'air-light' ),
      'sub_fields'   => [
        [
          'key'     => 'field_hpc_featured_spec_icon',
          'label'   => __( 'Ícono', 'air-light' ),
          'name'    => 'icon',
          'type'    => 'select',
          'choices' => $icon_choices,
        ],
        [
          'key'   => 'field_hpc_featured_spec_label',
          'label' => __( 'Texto', 'air-light' ),
          'name'  => 'label',
          'type'  => 'text',
        ],
      ],
    ],

    // How it works
    [
      'key'   => 'field_hpc_tab_how_it_works',
      'label' => __( '5. Cómo funciona', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_hpc_how_it_works_title',
      'label' => __( 'Título de la sección', 'air-light' ),
      'name'  => 'how_it_works_title',
      'type'  => 'text',
      'default_value' => 'Simplicidad en cada detalle',
    ],
    [
      'key'          => 'field_hpc_how_it_works_steps',
      'label'        => __( 'Pasos', 'air-light' ),
      'name'         => 'how_it_works_steps',
      'type'         => 'repeater',
      'min'          => 1,
      'max'          => 6,
      'layout'       => 'block',
      'button_label' => __( 'Agregar paso', 'air-light' ),
      'sub_fields'   => [
        [
          'key'          => 'field_hpc_step_image',
          'label'        => __( 'Imagen', 'air-light' ),
          'name'         => 'image',
          'type'         => 'image',
          'return_format' => 'array',
          'preview_size' => 'medium',
        ],
        [
          'key'   => 'field_hpc_step_title',
          'label' => __( 'Título', 'air-light' ),
          'name'  => 'title',
          'type'  => 'text',
        ],
        [
          'key'   => 'field_hpc_step_text',
          'label' => __( 'Texto', 'air-light' ),
          'name'  => 'text',
          'type'  => 'textarea',
          'rows'  => 2,
        ],
      ],
    ],

    // Emotional branding
    [
      'key'   => 'field_hpc_tab_emotional',
      'label' => __( '6. Branding emocional', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_hpc_emotional_image',
      'label'        => __( 'Imagen', 'air-light' ),
      'name'         => 'emotional_image',
      'type'         => 'image',
      'return_format' => 'array',
      'preview_size' => 'medium',
    ],
    [
      'key'   => 'field_hpc_emotional_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'emotional_title',
      'type'  => 'text',
      'default_value' => 'Vendemos tranquilidad',
    ],

    // Reviews
    [
      'key'   => 'field_hpc_tab_reviews',
      'label' => __( '7. Historias', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_hpc_reviews_title',
      'label'         => __( 'Título de la sección', 'air-light' ),
      'name'          => 'reviews_title',
      'type'          => 'text',
      'default_value' => 'Historias CANUT',
      'instructions'  => __( 'Las historias mostradas se gestionan desde el CPT Historias, marcando "Aparece en portada" en cada una.', 'air-light' ),
    ],

    // Warranty band
    [
      'key'   => 'field_hpc_tab_warranty',
      'label' => __( '8. Garantía', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'   => 'field_hpc_warranty_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'warranty_title',
      'type'  => 'text',
      'default_value' => 'Compromiso CANUT: 2 años de garantía total',
    ],
    [
      'key'   => 'field_hpc_warranty_text',
      'label' => __( 'Texto', 'air-light' ),
      'name'  => 'warranty_text',
      'type'  => 'text',
      'default_value' => 'Diseñados para durar toda una vida perruna.',
    ],
    [
      'key'   => 'field_hpc_warranty_cta_label',
      'label' => __( 'Texto del botón', 'air-light' ),
      'name'  => 'warranty_cta_label',
      'type'  => 'text',
      'default_value' => 'Más información',
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_hpc_warranty_cta_url',
      'label' => __( 'Enlace del botón', 'air-light' ),
      'name'  => 'warranty_cta_url',
      'type'  => 'url',
      'wrapper' => [ 'width' => '50' ],
    ],

    // Final CTA
    [
      'key'   => 'field_hpc_tab_final_cta',
      'label' => __( '9. CTA final', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_hpc_final_cta_image',
      'label'        => __( 'Imagen de fondo', 'air-light' ),
      'name'         => 'final_cta_image',
      'type'         => 'image',
      'return_format' => 'array',
      'preview_size' => 'medium',
    ],
    [
      'key'   => 'field_hpc_final_cta_title',
      'label' => __( 'Título', 'air-light' ),
      'name'  => 'final_cta_title',
      'type'  => 'text',
      'default_value' => 'El futuro del cuidado está aquí',
    ],
    [
      'key'   => 'field_hpc_final_cta_label',
      'label' => __( 'Texto del botón', 'air-light' ),
      'name'  => 'final_cta_label',
      'type'  => 'text',
      'default_value' => 'Ver comederos',
      'wrapper' => [ 'width' => '50' ],
    ],
    [
      'key'   => 'field_hpc_final_cta_url',
      'label' => __( 'Enlace del botón', 'air-light' ),
      'name'  => 'final_cta_url',
      'type'  => 'url',
      'wrapper' => [ 'width' => '50' ],
    ],

  ],
  'location' => [
    [
      [
        'param'    => 'block',
        'operator' => '==',
        'value'    => 'acf/homepage-canut',
      ],
    ],
  ],
] );
