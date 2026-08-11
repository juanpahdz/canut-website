<?php
/**
 * Field group for the CANUT single product page (woocommerce/single-product.php).
 *
 * Covers every marketing section on the product template that isn't already
 * native WooCommerce data (title, price, gallery, short description,
 * attributes). Each field falls back to the original CANUT/Figma copy when
 * left empty, the same way template-parts/front-page/*.php fall back.
 *
 * @package air-light
 */

namespace Air_Light;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
  return;
}

/**
 * Icon choices for the "Lo que incluye" chips (mini cards, see
 * views/_product.scss .tabs-canut-chip).
 */
$box_content_icon_choices = icon_choices();

acf_add_local_field_group( [
  'key'    => 'group_product_canut',
  'title'  => __( 'Página de producto CANUT', 'air-light' ),
  'fields' => [

    // Promoción por cantidad (fee automático, ver inc/hooks/quantity-discount.php).
    // Sin tab propia a propósito - son ajustes comerciales, no copy de marketing,
    // así que se muestran arriba de las pestañas en vez de dentro de una.
    [
      'key'          => 'field_pc_qty_discount_enabled',
      'label'        => __( 'Promoción "Lleva X y ahorra"', 'air-light' ),
      'name'         => 'qty_discount_enabled',
      'type'         => 'true_false',
      'ui'           => 1,
      'instructions' => __( 'Si se activa, el carrito muestra la promo de descuento por cantidad para este producto y aplica el % configurado al comprar la cantidad mínima o más.', 'air-light' ),
    ],
    [
      'key'               => 'field_pc_qty_discount_min_qty',
      'label'             => __( 'Cantidad mínima', 'air-light' ),
      'name'              => 'qty_discount_min_qty',
      'type'              => 'number',
      'default_value'     => 2,
      'min'               => 2,
      'wrapper'           => [ 'width' => '50' ],
      'conditional_logic' => [
        [
          [
            'field'    => 'field_pc_qty_discount_enabled',
            'operator' => '==',
            'value'    => '1',
          ],
        ],
      ],
    ],
    [
      'key'               => 'field_pc_qty_discount_percent',
      'label'             => __( 'Porcentaje de descuento', 'air-light' ),
      'name'              => 'qty_discount_percent',
      'type'              => 'number',
      'default_value'     => 10,
      'min'               => 1,
      'max'               => 100,
      'append'            => '%',
      'wrapper'           => [ 'width' => '50' ],
      'conditional_logic' => [
        [
          [
            'field'    => 'field_pc_qty_discount_enabled',
            'operator' => '==',
            'value'    => '1',
          ],
        ],
      ],
    ],

    // Encabezado
    [
      'key'   => 'field_pc_tab_header',
      'label' => __( '1. Encabezado', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_pc_badge_text',
      'label'        => __( 'Texto del badge', 'air-light' ),
      'name'         => 'badge_text',
      'type'         => 'text',
      'instructions' => __( 'Ej. "Más vendido". Déjalo vacío para usar el badge automático (Destacado/Oferta según el producto).', 'air-light' ),
    ],

    // Cómo funciona
    [
      'key'   => 'field_pc_tab_how_it_works',
      'label' => __( '2. Cómo funciona', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_pc_how_it_works_title',
      'label'         => __( 'Título de la sección', 'air-light' ),
      'name'          => 'how_it_works_title',
      'type'          => 'text',
      'default_value' => '¿Cómo funciona?',
    ],
    [
      'key'           => 'field_pc_how_it_works_steps',
      'label'         => __( 'Pasos', 'air-light' ),
      'name'          => 'how_it_works_steps',
      'type'          => 'repeater',
      'min'           => 1,
      'max'           => 6,
      'layout'        => 'block',
      'button_label'  => __( 'Agregar paso', 'air-light' ),
      'default_value' => [
        [ 'title' => 'Recibe tu comedero', 'text' => 'Entrega rápida en 1-3 días hábiles.' ],
        [ 'title' => 'Programa los horarios', 'text' => 'Configuración intuitiva vía CANUT App.' ],
        [ 'title' => 'Tu mascota come sola', 'text' => 'Dispensado automático de alta precisión.' ],
        [ 'title' => 'Vigila a tu mascota', 'text' => 'Cámara HD y monitoreo remoto 24/7.' ],
      ],
      'sub_fields'    => [
        [
          'key'   => 'field_pc_step_title',
          'label' => __( 'Título', 'air-light' ),
          'name'  => 'title',
          'type'  => 'text',
        ],
        [
          'key'   => 'field_pc_step_text',
          'label' => __( 'Texto', 'air-light' ),
          'name'  => 'text',
          'type'  => 'text',
        ],
      ],
    ],

    // Lo que incluye
    [
      'key'   => 'field_pc_tab_box_contents',
      'label' => __( '3. Lo que incluye', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_pc_box_contents',
      'label'         => __( 'Elementos de la caja', 'air-light' ),
      'name'          => 'box_contents',
      'type'          => 'repeater',
      'min'           => 1,
      'max'           => 8,
      'layout'        => 'table',
      'button_label'  => __( 'Agregar elemento', 'air-light' ),
      'default_value' => [
        [ 'text' => 'CANUT Feeder', 'icon' => 'package' ],
        [ 'text' => 'Cable USB-C', 'icon' => 'plug' ],
        [ 'text' => 'Batería de litio', 'icon' => 'battery-full' ],
        [ 'text' => 'Guía rápida', 'icon' => 'book-open' ],
      ],
      'sub_fields'    => [
        [
          'key'     => 'field_pc_box_content_icon',
          'label'   => __( 'Ícono', 'air-light' ),
          'name'    => 'icon',
          'type'    => 'select',
          'choices' => $box_content_icon_choices,
          'ui'      => 1,
          'wrapper' => [ 'width' => '30' ],
        ],
        [
          'key'     => 'field_pc_box_content_text',
          'label'   => __( 'Texto', 'air-light' ),
          'name'    => 'text',
          'type'    => 'text',
          'wrapper' => [ 'width' => '70' ],
        ],
      ],
    ],

    // FAQ corta (columna de compra)
    [
      'key'   => 'field_pc_tab_faq',
      'label' => __( '4. FAQ (columna de compra)', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_pc_faq_items',
      'label'         => __( 'Preguntas', 'air-light' ),
      'name'          => 'faq_items',
      'type'          => 'repeater',
      'min'           => 0,
      'max'           => 6,
      'layout'        => 'block',
      'button_label'  => __( 'Agregar pregunta', 'air-light' ),
      'default_value' => [
        [ 'question' => '¿Qué pasa si se daña?', 'answer' => 'Contamos con garantía de 1 año y servicio técnico local especializado. Siempre estarás cubierto.' ],
        [ 'question' => '¿Sirve para 2 o más perros?', 'answer' => 'Sí, puedes programar múltiples horarios y porciones para cubrir a más de una mascota.' ],
        [ 'question' => '¿Y si mi mascota sufre de ansiedad?', 'answer' => 'La rutina de horarios fijos ayuda a reducir la ansiedad por comida y a generar calma.' ],
      ],
      'sub_fields'    => [
        [
          'key'   => 'field_pc_faq_question',
          'label' => __( 'Pregunta', 'air-light' ),
          'name'  => 'question',
          'type'  => 'text',
        ],
        [
          'key'   => 'field_pc_faq_answer',
          'label' => __( 'Respuesta', 'air-light' ),
          'name'  => 'answer',
          'type'  => 'textarea',
          'rows'  => 2,
        ],
      ],
    ],

    // Comparación
    [
      'key'   => 'field_pc_tab_comparison',
      'label' => __( '5. Comparación', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_pc_comparison_title',
      'label'         => __( 'Título', 'air-light' ),
      'name'          => 'comparison_title',
      'type'          => 'text',
      'default_value' => '¿Se te hace familiar?',
    ],
    [
      'key'           => 'field_pc_comparison_scenarios',
      'label'         => __( 'Escenarios (imágenes pequeñas)', 'air-light' ),
      'name'          => 'comparison_scenarios',
      'type'          => 'repeater',
      'min'           => 0,
      'max'           => 4,
      'layout'        => 'block',
      'button_label'  => __( 'Agregar escenario', 'air-light' ),
      'default_value' => [
        [ 'text' => '¿Has llegado tarde y tu perro sigue esperando?' ],
        [ 'text' => 'El caos de las reuniones que nunca terminan.' ],
        [ 'text' => 'La culpa de no estar ahí cuando te necesitan.' ],
      ],
      'sub_fields'    => [
        [
          'key'           => 'field_pc_comparison_scenario_image',
          'label'         => __( 'Imagen', 'air-light' ),
          'name'          => 'image',
          'type'          => 'image',
          'return_format' => 'array',
          'preview_size'  => 'medium',
        ],
        [
          'key'   => 'field_pc_comparison_scenario_text',
          'label' => __( 'Texto', 'air-light' ),
          'name'  => 'text',
          'type'  => 'text',
        ],
      ],
    ],
    [
      'key'           => 'field_pc_comparison_card_image',
      'label'         => __( 'Imagen de la tarjeta grande', 'air-light' ),
      'name'          => 'comparison_card_image',
      'type'          => 'image',
      'return_format' => 'array',
      'preview_size'  => 'medium',
    ],
    [
      'key'           => 'field_pc_comparison_card_title',
      'label'         => __( 'Título de la tarjeta grande', 'air-light' ),
      'name'          => 'comparison_card_title',
      'type'          => 'text',
      'default_value' => 'Con CANUT, esto ya no pasa.',
    ],
    [
      'key'           => 'field_pc_comparison_card_text',
      'label'         => __( 'Texto de la tarjeta grande', 'air-light' ),
      'name'          => 'comparison_card_text',
      'type'          => 'textarea',
      'rows'          => 3,
      'default_value' => 'Transforma la angustia en serenidad. Tu mascota merece la puntualidad de un reloj y el cariño de una marca premium.',
    ],
    [
      'key'           => 'field_pc_comparison_card_link_label',
      'label'         => __( 'Enlace: texto', 'air-light' ),
      'name'          => 'comparison_card_link_label',
      'type'          => 'text',
      'default_value' => 'Ver tecnología',
      'wrapper'       => [ 'width' => '50' ],
    ],
    [
      'key'     => 'field_pc_comparison_card_link_url',
      'label'   => __( 'Enlace: URL', 'air-light' ),
      'name'    => 'comparison_card_link_url',
      'type'    => 'url',
      'wrapper' => [ 'width' => '50' ],
    ],

    // Familia CANUT (galería estilo de vida)
    [
      'key'   => 'field_pc_tab_gallery_strip',
      'label' => __( '6. Familia CANUT (galería)', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_pc_gallery_strip_title',
      'label'         => __( 'Título', 'air-light' ),
      'name'          => 'gallery_strip_title',
      'type'          => 'text',
      'default_value' => 'Familia CANUT',
    ],
    [
      'key'          => 'field_pc_gallery_strip_images',
      'label'        => __( 'Imágenes / Videos', 'air-light' ),
      'name'         => 'gallery_strip_images',
      'type'         => 'repeater',
      'min'          => 0,
      'max'          => 8,
      'layout'       => 'table',
      'button_label' => __( 'Agregar elemento', 'air-light' ),
      'sub_fields'   => [
        [
          'key'           => 'field_pc_gallery_strip_image',
          'label'         => __( 'Imagen', 'air-light' ),
          'name'          => 'image',
          'type'          => 'image',
          'return_format' => 'array',
          'preview_size'  => 'medium',
          'instructions'  => __( 'Se usa sola si no hay video, y como poster (fotograma inicial) mientras el video carga.', 'air-light' ),
        ],
        [
          'key'           => 'field_pc_gallery_strip_video',
          'label'         => __( 'Video (opcional)', 'air-light' ),
          'name'          => 'video',
          'type'          => 'file',
          'return_format' => 'array',
          'mime_types'    => 'mp4,webm,mov',
          'instructions'  => __( 'Clip corto en loop (5-6 segundos, sin audio). Si se deja vacío, se usa la imagen de arriba.', 'air-light' ),
        ],
        [
          'key'           => 'field_pc_gallery_strip_caption',
          'label'         => __( 'Etiqueta', 'air-light' ),
          'name'          => 'caption',
          'type'          => 'text',
          'default_value' => 'Cliente satisfecho',
        ],
      ],
    ],

    // Reseñas
    [
      'key'   => 'field_pc_tab_reviews',
      'label' => __( '7. Reseñas', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_pc_reviews_title',
      'label'         => __( 'Título', 'air-light' ),
      'name'          => 'reviews_title',
      'type'          => 'text',
      'default_value' => 'Nuestros Clientes',
    ],
    [
      'key'           => 'field_pc_reviews_rating_score',
      'label'         => __( 'Calificación', 'air-light' ),
      'name'          => 'reviews_rating_score',
      'type'          => 'text',
      'default_value' => '4.9',
      'wrapper'       => [ 'width' => '50' ],
    ],
    [
      'key'           => 'field_pc_reviews_rating_count',
      'label'         => __( 'Texto del contador', 'air-light' ),
      'name'          => 'reviews_rating_count',
      'type'          => 'text',
      'default_value' => '(124 reseñas)',
      'wrapper'       => [ 'width' => '50' ],
    ],
    [
      'key'           => 'field_pc_reviews',
      'label'         => __( 'Reseñas', 'air-light' ),
      'name'          => 'reviews',
      'type'          => 'repeater',
      'min'           => 0,
      'max'           => 6,
      'layout'        => 'block',
      'button_label'  => __( 'Agregar reseña', 'air-light' ),
      'default_value' => [
        [ 'name' => 'Carolina M.', 'text' => 'Cambió mi vida y la de mi gato. Ya no me despierta a las 5 am para pedir comida. El diseño en madera es espectacular.' ],
        [ 'name' => 'Andrés F.', 'text' => 'La cámara tiene una resolución increíble. Puedo ver a mi perro desde la oficina y hablarle. Es muy robusto y elegante.' ],
        [ 'name' => 'Valentina R.', 'text' => 'La atención al cliente por WhatsApp es 10/10. Me ayudaron a configurar todo en 5 minutos. El mejor regalo que le he dado a mi mascota.' ],
      ],
      'sub_fields'    => [
        [
          'key'   => 'field_pc_review_name',
          'label' => __( 'Nombre', 'air-light' ),
          'name'  => 'name',
          'type'  => 'text',
        ],
        [
          'key'   => 'field_pc_review_text',
          'label' => __( 'Texto', 'air-light' ),
          'name'  => 'text',
          'type'  => 'textarea',
          'rows'  => 2,
        ],
        [
          'key'           => 'field_pc_review_image',
          'label'         => __( 'Imagen', 'air-light' ),
          'name'          => 'image',
          'type'          => 'image',
          'return_format' => 'array',
          'preview_size'  => 'medium',
        ],
      ],
    ],

    // FAQ extendida
    [
      'key'   => 'field_pc_tab_faq_extended',
      'label' => __( '8. FAQ extendida', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_pc_extended_faq_title',
      'label'         => __( 'Título', 'air-light' ),
      'name'          => 'extended_faq_title',
      'type'          => 'text',
      'default_value' => 'Preguntas Frecuentes',
    ],
    [
      'key'           => 'field_pc_extended_faq_image',
      'label'         => __( 'Imagen por defecto', 'air-light' ),
      'name'          => 'extended_faq_image',
      'type'          => 'image',
      'return_format' => 'array',
      'preview_size'  => 'medium',
      'instructions'  => __( 'Se usa mientras no hay ninguna pregunta abierta, y para las preguntas que no tengan su propia imagen.', 'air-light' ),
    ],
    [
      'key'           => 'field_pc_extended_faq_items',
      'label'         => __( 'Preguntas', 'air-light' ),
      'name'          => 'extended_faq_items',
      'type'          => 'repeater',
      'min'           => 0,
      'max'           => 10,
      'layout'        => 'block',
      'button_label'  => __( 'Agregar pregunta', 'air-light' ),
      'default_value' => [
        [ 'question' => '¿Qué pasa si se daña?', 'answer' => 'Contamos con garantía de 1 año y servicio técnico local especializado. Siempre estarás cubierto.' ],
        [ 'question' => '¿Sirve para 2 o más perros?', 'answer' => 'Sí, puedes programar múltiples horarios y porciones para cubrir a más de una mascota.' ],
        [ 'question' => '¿Funciona con comida húmeda o solo seca?', 'answer' => 'Está diseñado para alimento seco; los mecanismos de precisión no están pensados para comida húmeda.' ],
        [ 'question' => '¿Es difícil de configurar?', 'answer' => 'No, la app CANUT te guía paso a paso y toma menos de 5 minutos.' ],
        [ 'question' => '¿Para qué tipo de mascota funciona?', 'answer' => 'Funciona muy bien para perros y gatos de cualquier tamaño.' ],
        [ 'question' => '¿Y si mi mascota sufre de ansiedad?', 'answer' => 'La rutina de horarios fijos ayuda a reducir la ansiedad por comida y a generar calma.' ],
      ],
      'sub_fields'    => [
        [
          'key'   => 'field_pc_extended_faq_question',
          'label' => __( 'Pregunta', 'air-light' ),
          'name'  => 'question',
          'type'  => 'text',
        ],
        [
          'key'   => 'field_pc_extended_faq_answer',
          'label' => __( 'Respuesta', 'air-light' ),
          'name'  => 'answer',
          'type'  => 'textarea',
          'rows'  => 2,
        ],
        [
          'key'           => 'field_pc_extended_faq_item_image',
          'label'         => __( 'Imagen (opcional)', 'air-light' ),
          'name'          => 'image',
          'type'          => 'image',
          'return_format' => 'array',
          'preview_size'  => 'medium',
          'instructions'  => __( 'Si se deja vacío, se usa la imagen por defecto de la sección al abrir esta pregunta.', 'air-light' ),
        ],
      ],
    ],

    // CTA final
    [
      'key'   => 'field_pc_tab_help_cta',
      'label' => __( '9. CTA final', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'           => 'field_pc_help_cta_title',
      'label'         => __( 'Título', 'air-light' ),
      'name'          => 'help_cta_title',
      'type'          => 'text',
      'default_value' => 'Nuestro equipo te ayuda a elegir el comedero ideal',
    ],
    [
      'key'           => 'field_pc_help_cta_text',
      'label'         => __( 'Texto', 'air-light' ),
      'name'          => 'help_cta_text',
      'type'          => 'textarea',
      'rows'          => 2,
      'default_value' => '¿No estás seguro de cuál modelo se adapta mejor a tu mascota? Chatea con nuestros expertos en cuidado animal.',
    ],

    // Datos estructurados (JSON-LD), ver inc/hooks/structured-data.php
    [
      'key'   => 'field_pc_tab_structured_data',
      'label' => __( '10. Datos estructurados (SEO)', 'air-light' ),
      'type'  => 'tab',
    ],
    [
      'key'          => 'field_pc_structured_data_brand',
      'label'        => __( 'Marca', 'air-light' ),
      'name'         => 'structured_data_brand',
      'type'         => 'text',
      'instructions' => __( 'Se publica como la marca ("brand") de este producto en los datos estructurados que lee Google.', 'air-light' ),
      'default_value' => 'CANUT',
      'wrapper'      => [ 'width' => '34' ],
    ],
    [
      'key'           => 'field_pc_structured_data_return_days',
      'label'         => __( 'Devolución: días', 'air-light' ),
      'name'          => 'structured_data_return_days',
      'type'          => 'number',
      'instructions'  => __( 'Debe coincidir con lo mostrado en la página ("Retracto 5 días") y la política real de este producto.', 'air-light' ),
      'default_value' => 5,
      'min'           => 0,
      'wrapper'       => [ 'width' => '33' ],
    ],
    [
      'key'           => 'field_pc_structured_data_return_method',
      'label'         => __( 'Devolución: método', 'air-light' ),
      'name'          => 'structured_data_return_method',
      'type'          => 'select',
      'choices'       => [
        'https://schema.org/ReturnByMail'  => __( 'Envío por mensajería', 'air-light' ),
        'https://schema.org/ReturnInStore' => __( 'En tienda física', 'air-light' ),
        'https://schema.org/ReturnAtKiosk' => __( 'En punto de recolección', 'air-light' ),
      ],
      'default_value' => 'https://schema.org/ReturnByMail',
      'wrapper'       => [ 'width' => '33' ],
    ],
    [
      'key'           => 'field_pc_structured_data_return_fees',
      'label'         => __( 'Devolución: ¿quién paga el envío?', 'air-light' ),
      'name'          => 'structured_data_return_fees',
      'type'          => 'select',
      'choices'       => [
        'https://schema.org/FreeReturn'                      => __( 'Devolución gratis', 'air-light' ),
        'https://schema.org/ReturnFeesCustomerResponsibility' => __( 'El cliente paga el envío', 'air-light' ),
        'https://schema.org/ReturnShippingFees'              => __( 'Cobra tarifa fija de envío', 'air-light' ),
      ],
      'default_value' => 'https://schema.org/ReturnFeesCustomerResponsibility',
      'wrapper'       => [ 'width' => '33' ],
    ],
    [
      'key'           => 'field_pc_structured_data_shipping_days_min',
      'label'         => __( 'Envío: días mínimos', 'air-light' ),
      'name'          => 'structured_data_shipping_days_min',
      'type'          => 'number',
      'instructions'  => __( 'Debe coincidir con lo mostrado en la página ("Envío 1-3 días").', 'air-light' ),
      'default_value' => 1,
      'min'           => 0,
      'wrapper'       => [ 'width' => '33' ],
    ],
    [
      'key'           => 'field_pc_structured_data_shipping_days_max',
      'label'         => __( 'Envío: días máximos', 'air-light' ),
      'name'          => 'structured_data_shipping_days_max',
      'type'          => 'number',
      'default_value' => 3,
      'min'           => 0,
      'wrapper'       => [ 'width' => '33' ],
    ],
    [
      'key'           => 'field_pc_structured_data_shipping_cost',
      'label'         => __( 'Envío: costo', 'air-light' ),
      'name'          => 'structured_data_shipping_cost',
      'type'          => 'number',
      'instructions'  => __( 'En la moneda de la tienda. Usa 0 si el envío de este producto es gratis.', 'air-light' ),
      'default_value' => 0,
      'min'           => 0,
      'step'          => 0.01,
      'wrapper'       => [ 'width' => '34' ],
    ],

  ],
  'location' => [
    [
      [
        'param'    => 'post_type',
        'operator' => '==',
        'value'    => 'product',
      ],
    ],
  ],
] );
