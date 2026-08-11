<?php
/**
 * Single product page.
 *
 * Overrides WooCommerce's default single-product.php. The header/footer are
 * the theme's own. Gallery, purchase details and add-to-cart/buy-now use the
 * real product data. Every marketing section below (how it works, lo que
 * incluye, FAQ, comparison, lifestyle gallery, reviews, extended FAQ, help
 * CTA) is configurable per-product via the "Página de producto CANUT" ACF
 * field group (inc/acf-fields/product-canut.php), each falling back to the
 * original CANUT/Figma copy when a field is left empty - same fallback
 * pattern as template-parts/front-page/*.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package air-light
 */

namespace Air_Light;

defined( 'ABSPATH' ) || exit;

get_header();

do_action( 'woocommerce_before_main_content' );

while ( have_posts() ) :
  the_post();

  global $product;

  if ( ! $product instanceof \WC_Product ) {
    $product = wc_get_product( get_the_ID() );
  }

  if ( ! $product instanceof \WC_Product ) {
    continue;
  }

  $gallery_image_ids = array_values( array_unique( array_filter( array_merge(
    [ $product->get_image_id() ],
    $product->get_gallery_image_ids()
  ) ) ) );

  if ( ! $gallery_image_ids ) {
    $gallery_image_ids = [ 0 ];
  }

  $main_image_url = $gallery_image_ids[0]
    ? wp_get_attachment_image_url( $gallery_image_ids[0], 'large' )
    : wc_placeholder_img_src( 'large' );

  /**
   * A gallery attachment can be a video (WooCommerce's own "Add product
   * gallery images" media frame allows picking any media type, not just
   * images), so branch by mime type instead of assuming every ID is an
   * image.
   */
  $gallery_items = array_map( function ( $image_id ) {
    $mime_type = get_post_mime_type( $image_id );

    return [
      'id'       => $image_id,
      'is_video' => $mime_type && str_starts_with( $mime_type, 'video/' ),
      'mime'     => $mime_type,
    ];
  }, $gallery_image_ids );

  $buy_now_url = add_query_arg( 'add-to-cart', $product->get_id(), wc_get_checkout_url() );

  /**
   * If this product is already in the cart, "Comprar ahora" turns into
   * "Finalizar compra" and goes straight to checkout instead of adding
   * another unit and opening the drawer - same server-rendered state
   * pattern as $already_in_cart in woocommerce/content-product.php.
   */
  $already_in_cart = function_exists( 'WC' ) && WC()->cart
    && WC()->cart->find_product_in_cart( WC()->cart->generate_cart_id( $product->get_id() ) );

  $whatsapp_url = get_whatsapp_url( 'ventas', sprintf(
    /* translators: %s: product name. */
    __( 'Hola, quiero comprar %s', 'air-light' ),
    html_entity_decode( get_the_title(), ENT_QUOTES )
  ) );

  /**
   * ACF-backed content for the marketing sections below, each falling back
   * to the original Figma copy when the "Página de producto CANUT" field
   * group is empty or ACF isn't active.
   */
  $has_acf = function_exists( 'get_field' );
  $field   = function ( $name ) use ( $has_acf ) {
    return $has_acf ? get_field( $name ) : null;
  };
  $rows    = function ( $name ) use ( $has_acf ) {
    return $has_acf && have_rows( $name );
  };

  $badge_text = $field( 'badge_text' );

  $how_it_works_title = $field( 'how_it_works_title' ) ?: __( '¿Cómo funciona?', 'air-light' );
  $how_it_works_steps = [];

  if ( $rows( 'how_it_works_steps' ) ) {
    while ( have_rows( 'how_it_works_steps' ) ) {
      the_row();
      $how_it_works_steps[] = [ get_sub_field( 'title' ), get_sub_field( 'text' ) ];
    }
  } else {
    $how_it_works_steps = [
      [ __( 'Recibe tu comedero', 'air-light' ), __( 'Entrega rápida en 1-3 días hábiles.', 'air-light' ) ],
      [ __( 'Programa los horarios', 'air-light' ), __( 'Configuración intuitiva vía CANUT App.', 'air-light' ) ],
      [ __( 'Tu mascota come sola', 'air-light' ), __( 'Dispensado automático de alta precisión.', 'air-light' ) ],
      [ __( 'Vigila a tu mascota', 'air-light' ), __( 'Cámara HD y monitoreo remoto 24/7.', 'air-light' ) ],
    ];
  }

  /**
   * Best-effort icon slug for a WooCommerce attribute name (Especificaciones
   * tab has no ACF field of its own to pick an icon from, since it mirrors
   * real product attributes). Matches assets/svg/icon-*.svg.
   */
  $attribute_icon = function ( $attribute_name ) {
    $name = remove_accents( mb_strtolower( $attribute_name ) );

    if ( str_contains( $name, 'capacidad' ) || str_contains( $name, 'volumen' ) ) {
      return 'drop';
    }

    if ( str_contains( $name, 'conectividad' ) || str_contains( $name, 'wifi' ) || str_contains( $name, 'bluetooth' ) ) {
      return 'wifi-high';
    }

    if ( str_contains( $name, 'material' ) ) {
      return 'cube';
    }

    if ( str_contains( $name, 'peso' ) || str_contains( $name, 'dimens' ) || str_contains( $name, 'tamaño' ) ) {
      return 'scales';
    }

    if ( str_contains( $name, 'batería' ) || str_contains( $name, 'bateria' ) ) {
      return 'battery-full';
    }

    return 'check';
  };

  $box_contents = [];

  if ( $rows( 'box_contents' ) ) {
    while ( have_rows( 'box_contents' ) ) {
      the_row();
      $box_contents[] = [
        'icon' => get_sub_field( 'icon' ) ?: 'check',
        'text' => get_sub_field( 'text' ),
      ];
    }
  } else {
    $box_contents = [
      [ 'icon' => 'package', 'text' => __( 'CANUT Feeder', 'air-light' ) ],
      [ 'icon' => 'plug', 'text' => __( 'Cable USB-C', 'air-light' ) ],
      [ 'icon' => 'battery-full', 'text' => __( 'Batería de litio', 'air-light' ) ],
      [ 'icon' => 'book-open', 'text' => __( 'Guía rápida', 'air-light' ) ],
    ];
  }

  $faq_items = [];

  if ( $rows( 'faq_items' ) ) {
    while ( have_rows( 'faq_items' ) ) {
      the_row();
      $faq_items[] = [ get_sub_field( 'question' ), get_sub_field( 'answer' ) ];
    }
  } else {
    $faq_items = [
      [ __( '¿Qué pasa si se daña?', 'air-light' ), __( 'Contamos con garantía de 1 año y servicio técnico local especializado. Siempre estarás cubierto.', 'air-light' ) ],
      [ __( '¿Sirve para 2 o más perros?', 'air-light' ), __( 'Sí, puedes programar múltiples horarios y porciones para cubrir a más de una mascota.', 'air-light' ) ],
      [ __( '¿Y si mi mascota sufre de ansiedad?', 'air-light' ), __( 'La rutina de horarios fijos ayuda a reducir la ansiedad por comida y a generar calma.', 'air-light' ) ],
    ];
  }

  $comparison_title     = $field( 'comparison_title' ) ?: __( '¿Se te hace familiar?', 'air-light' );
  $comparison_scenarios = [];

  if ( $rows( 'comparison_scenarios' ) ) {
    while ( have_rows( 'comparison_scenarios' ) ) {
      the_row();
      $scenario_image         = get_sub_field( 'image' );
      $comparison_scenarios[] = [
        'image' => $scenario_image['url'] ?? '',
        'text'  => get_sub_field( 'text' ),
      ];
    }
  } else {
    $comparison_scenarios = [
      [ 'image' => get_theme_file_uri( 'assets/images/homepage/how-it-works-1.jpg' ), 'text' => __( '¿Has llegado tarde y tu perro sigue esperando?', 'air-light' ) ],
      [ 'image' => get_theme_file_uri( 'assets/images/homepage/how-it-works-2.jpg' ), 'text' => __( 'El caos de las reuniones que nunca terminan.', 'air-light' ) ],
      [ 'image' => get_theme_file_uri( 'assets/images/homepage/how-it-works-3.jpg' ), 'text' => __( 'La culpa de no estar ahí cuando te necesitan.', 'air-light' ) ],
    ];
  }

  $gallery_strip_title  = $field( 'gallery_strip_title' ) ?: __( 'Familia CANUT', 'air-light' );
  $gallery_strip_images = [];

  if ( $rows( 'gallery_strip_images' ) ) {
    while ( have_rows( 'gallery_strip_images' ) ) {
      the_row();
      $strip_image = get_sub_field( 'image' );

      if ( empty( $strip_image['url'] ) ) {
        continue;
      }

      $strip_video = get_sub_field( 'video' );

      $gallery_strip_images[] = [
        'image'      => $strip_image['url'],
        'video'      => $strip_video['url'] ?? '',
        'video_mime' => $strip_video['mime_type'] ?? '',
        'caption'    => get_sub_field( 'caption' ) ?: __( 'Cliente satisfecho', 'air-light' ),
      ];
    }
  }

  if ( ! $gallery_strip_images ) {
    foreach ( [
      get_theme_file_uri( 'assets/images/homepage/hero-dog-feeder.jpg' ),
      get_theme_file_uri( 'assets/images/homepage/lifestyle-living-room.jpg' ),
      get_theme_file_uri( 'assets/images/homepage/how-it-works-1.jpg' ),
      get_theme_file_uri( 'assets/images/homepage/how-it-works-2.jpg' ),
      get_theme_file_uri( 'assets/images/homepage/how-it-works-3.jpg' ),
    ] as $image ) {
      $gallery_strip_images[] = [ 'image' => $image, 'caption' => __( 'Cliente satisfecho', 'air-light' ) ];
    }
  }

  /**
   * "Nuestros Clientes" is now driven entirely by Historia posts linked to
   * this product (its `product` ACF field, see inc/acf-fields/historia.php)
   * instead of the old per-product `reviews` repeater - see
   * historia_query_for_product()/historia_rating_summary_for_product()
   * (inc/hooks/historia.php). No placeholder data: when the product has no
   * linked stories yet, $reviews_summary is null and $reviews is empty, and
   * the render below shows an empty state inviting a visitor to write the
   * first one instead of a fake score/reviews.
   */
  $reviews_title   = $field( 'reviews_title' ) ?: __( 'Nuestros Clientes', 'air-light' );
  $reviews         = historia_query_for_product( $product->get_id(), 6 );
  $reviews_summary = historia_rating_summary_for_product( $product->get_id() );

  $extended_faq_title       = $field( 'extended_faq_title' ) ?: __( 'Preguntas Frecuentes', 'air-light' );
  $extended_faq_image_field = $field( 'extended_faq_image' );
  $extended_faq_image       = $extended_faq_image_field['url'] ?? get_theme_file_uri( 'assets/images/homepage/emotional-branding.jpg' );
  $extended_faq_items       = [];

  if ( $rows( 'extended_faq_items' ) ) {
    while ( have_rows( 'extended_faq_items' ) ) {
      the_row();
      $item_image            = get_sub_field( 'image' );
      $extended_faq_items[] = [
        'question' => get_sub_field( 'question' ),
        'answer'   => get_sub_field( 'answer' ),
        'image'    => $item_image['url'] ?? $extended_faq_image,
      ];
    }
  } else {
    $extended_faq_items = [
      [ 'question' => __( '¿Qué pasa si se daña?', 'air-light' ), 'answer' => __( 'Contamos con garantía de 1 año y servicio técnico local especializado. Siempre estarás cubierto.', 'air-light' ), 'image' => get_theme_file_uri( 'assets/images/homepage/emotional-branding.jpg' ) ],
      [ 'question' => __( '¿Sirve para 2 o más perros?', 'air-light' ), 'answer' => __( 'Sí, puedes programar múltiples horarios y porciones para cubrir a más de una mascota.', 'air-light' ), 'image' => get_theme_file_uri( 'assets/images/homepage/hero-dog-feeder.jpg' ) ],
      [ 'question' => __( '¿Funciona con comida húmeda o solo seca?', 'air-light' ), 'answer' => __( 'Está diseñado para alimento seco; los mecanismos de precisión no están pensados para comida húmeda.', 'air-light' ), 'image' => get_theme_file_uri( 'assets/images/homepage/how-it-works-2.jpg' ) ],
      [ 'question' => __( '¿Es difícil de configurar?', 'air-light' ), 'answer' => __( 'No, la app CANUT te guía paso a paso y toma menos de 5 minutos.', 'air-light' ), 'image' => get_theme_file_uri( 'assets/images/homepage/how-it-works-1.jpg' ) ],
      [ 'question' => __( '¿Para qué tipo de mascota funciona?', 'air-light' ), 'answer' => __( 'Funciona muy bien para perros y gatos de cualquier tamaño.', 'air-light' ), 'image' => get_theme_file_uri( 'assets/images/homepage/lifestyle-living-room.jpg' ) ],
      [ 'question' => __( '¿Y si mi mascota sufre de ansiedad?', 'air-light' ), 'answer' => __( 'La rutina de horarios fijos ayuda a reducir la ansiedad por comida y a generar calma.', 'air-light' ), 'image' => get_theme_file_uri( 'assets/images/homepage/how-it-works-3.jpg' ) ],
    ];
  }

  $help_cta_title        = $field( 'help_cta_title' ) ?: __( 'Nuestro equipo te ayuda a elegir el comedero ideal', 'air-light' );
  $help_cta_text         = $field( 'help_cta_text' ) ?: __( '¿No estás seguro de cuál modelo se adapta mejor a tu mascota? Chatea con nuestros expertos en cuidado animal.', 'air-light' );
  $help_cta_whatsapp_url = get_whatsapp_url( 'soporte' );

  ?>

  <div class="wrap-canut product-breadcrumb-wrap">
    <?php woocommerce_breadcrumb(); ?>
  </div>

  <div class="wrap-canut product-main">

    <div class="product-gallery" data-product-gallery>
      <?php if ( count( $gallery_items ) > 1 ) : ?>
        <div class="product-gallery-thumbs-wrap" data-product-gallery-thumbs-wrap>
          <button type="button" class="product-gallery-thumbs-arrow is-prev" data-product-gallery-thumbs-prev aria-label="<?php esc_attr_e( 'Ver miniaturas anteriores', 'air-light' ); ?>">
            <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
          </button>

          <div class="product-gallery-thumbs" data-product-gallery-thumbs>
            <?php foreach ( $gallery_items as $index => $item ) : ?>
              <button
                type="button"
                class="product-gallery-thumb<?php echo $item['is_video'] ? ' is-video' : ''; ?><?php echo 0 === $index ? ' is-active' : ''; ?>"
                data-product-gallery-thumb
                data-index="<?php echo esc_attr( $index ); ?>"
              >
                <?php if ( $item['is_video'] ) : ?>
                  <video muted playsinline preload="metadata">
                    <source src="<?php echo esc_url( wp_get_attachment_url( $item['id'] ) ); ?>#t=0.1" <?php echo $item['mime'] ? 'type="' . esc_attr( $item['mime'] ) . '"' : ''; ?>>
                  </video>
                  <?php require get_theme_file_path( 'assets/svg/icon-play-circle.svg' ); ?>
                <?php else : ?>
                  <img src="<?php echo esc_url( wp_get_attachment_image_url( $item['id'], 'thumbnail' ) ); ?>" alt="" loading="lazy">
                <?php endif; ?>
              </button>
            <?php endforeach; ?>
          </div>

          <button type="button" class="product-gallery-thumbs-arrow is-next" data-product-gallery-thumbs-next aria-label="<?php esc_attr_e( 'Ver más miniaturas', 'air-light' ); ?>">
            <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
          </button>
        </div>
      <?php endif; ?>

      <div class="product-gallery-main">
        <div class="product-gallery-track-wrap">
          <div class="product-gallery-track" data-product-gallery-track>
            <?php foreach ( $gallery_items as $index => $item ) : ?>
              <?php if ( $item['is_video'] ) : ?>
                <div
                  class="product-gallery-slide is-video<?php echo 0 === $index ? ' is-active' : ''; ?>"
                  data-product-gallery-slide
                  data-index="<?php echo esc_attr( $index ); ?>"
                >
                  <video controls playsinline preload="metadata">
                    <source src="<?php echo esc_url( wp_get_attachment_url( $item['id'] ) ); ?>" <?php echo $item['mime'] ? 'type="' . esc_attr( $item['mime'] ) . '"' : ''; ?>>
                  </video>
                </div>
              <?php else : ?>
                <button
                  type="button"
                  class="product-gallery-slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
                  data-product-gallery-slide
                  data-index="<?php echo esc_attr( $index ); ?>"
                  data-zoom="<?php echo esc_url( wp_get_attachment_image_url( $item['id'], 'full' ) ); ?>"
                  aria-label="<?php esc_attr_e( 'Ampliar imagen', 'air-light' ); ?>"
                >
                  <img
                    src="<?php echo esc_url( wp_get_attachment_image_url( $item['id'], 'large' ) ); ?>"
                    alt="<?php echo esc_attr( get_the_title() ); ?>"
                    loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
                  >
                </button>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>

          <?php if ( count( $gallery_items ) > 1 ) : ?>
            <button type="button" class="product-gallery-nav is-prev" data-product-gallery-prev aria-label="<?php esc_attr_e( 'Foto anterior', 'air-light' ); ?>">
              <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
            </button>
            <button type="button" class="product-gallery-nav is-next" data-product-gallery-next aria-label="<?php esc_attr_e( 'Foto siguiente', 'air-light' ); ?>">
              <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
            </button>
          <?php endif; ?>
        </div>

        <?php if ( count( $gallery_items ) > 1 ) : ?>
          <div class="product-gallery-dots" data-product-gallery-dots>
            <?php foreach ( $gallery_items as $index => $item ) : ?>
              <button
                type="button"
                class="product-gallery-dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
                data-product-gallery-dot
                data-index="<?php echo esc_attr( $index ); ?>"
                aria-label="<?php echo esc_attr( sprintf(
                  /* translators: %d: image number. */
                  __( 'Ver imagen %d', 'air-light' ),
                  $index + 1
                ) ); ?>"
              ></button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="product-details">

      <div class="product-details-header">
        <?php if ( $badge_text ) : ?>
          <span class="badge-canut"><?php echo esc_html( $badge_text ); ?></span>
        <?php elseif ( $product->is_featured() ) : ?>
          <span class="badge-canut"><?php esc_html_e( 'Más vendido', 'air-light' ); ?></span>
        <?php elseif ( $product->is_on_sale() ) : ?>
          <span class="badge-canut"><?php esc_html_e( 'Oferta', 'air-light' ); ?></span>
        <?php endif; ?>

        <h1 class="product-title"><?php echo esc_html( get_the_title() ); ?></h1>
        <p class="product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
      </div>

      <?php if ( $product->get_short_description() ) : ?>
        <p class="product-quote">&ldquo;<?php echo esc_html( wp_strip_all_tags( $product->get_short_description() ) ); ?>&rdquo;</p>
      <?php endif; ?>

      <div class="product-how-it-works">
        <h3 class="product-how-it-works-title"><?php echo esc_html( $how_it_works_title ); ?></h3>
        <ol class="product-how-it-works-list">
          <?php foreach ( $how_it_works_steps as $index => $step ) : ?>
            <li class="product-how-it-works-step">
              <span class="product-how-it-works-number"><?php echo esc_html( $index + 1 ); ?></span>
              <div>
                <p class="product-how-it-works-step-title"><?php echo esc_html( $step[0] ); ?></p>
                <p class="product-how-it-works-step-text"><?php echo esc_html( $step[1] ); ?></p>
              </div>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>

      <div data-tabs-canut>
        <div class="tabs-canut-nav">
          <button type="button" class="tabs-canut-trigger is-active" data-tabs-canut-trigger="incluye"><?php esc_html_e( 'Lo que incluye', 'air-light' ); ?></button>
          <button type="button" class="tabs-canut-trigger" data-tabs-canut-trigger="especificaciones"><?php esc_html_e( 'Especificaciones', 'air-light' ); ?></button>
        </div>

        <div class="tabs-canut-panel is-active" data-tabs-canut-panel="incluye">
          <ul class="tabs-canut-chips">
            <?php foreach ( $box_contents as $item ) : ?>
              <li class="tabs-canut-chip">
                <?php require get_theme_file_path( 'assets/svg/icon-' . $item['icon'] . '.svg' ); ?>
                <?php echo esc_html( $item['text'] ); ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="tabs-canut-panel" data-tabs-canut-panel="especificaciones">
          <?php $attributes = $product->get_attributes(); ?>
          <?php if ( $attributes ) : ?>
            <ul class="tabs-canut-chips">
              <?php foreach ( $attributes as $attribute ) :
                if ( $attribute->is_taxonomy() ) {
                  $values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), [ 'fields' => 'names' ] );
                } else {
                  $values = $attribute->get_options();
                }
                ?>
                <li class="tabs-canut-chip">
                  <?php require get_theme_file_path( 'assets/svg/icon-' . $attribute_icon( $attribute->get_name() ) . '.svg' ); ?>
                  <?php echo esc_html( wc_attribute_label( $attribute->get_name() ) . ': ' . implode( ', ', $values ) ); ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else : ?>
            <p class="product-how-it-works-step-text"><?php esc_html_e( 'Aún no se han definido especificaciones para este producto.', 'air-light' ); ?></p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ( $faq_items ) : ?>
        <div class="product-faq">
          <?php foreach ( $faq_items as $faq ) : ?>
            <details class="accordion-canut-item">
              <summary class="accordion-canut-trigger">
                <?php echo esc_html( $faq[0] ); ?>
                <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
              </summary>
              <p class="accordion-canut-panel"><?php echo esc_html( $faq[1] ); ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="product-purchase">
        <?php if ( $product->is_purchasable() && $product->is_in_stock() && $product->supports( 'ajax_add_to_cart' ) ) : ?>
          <a
            href="<?php echo esc_url( $already_in_cart ? wc_get_checkout_url() : $product->add_to_cart_url() ); ?>"
            data-quantity="1"
            data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
            data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
            data-added-label="<?php esc_attr_e( 'Finalizar compra', 'air-light' ); ?>"
            data-added-aria-label="<?php esc_attr_e( 'Finalizar compra', 'air-light' ); ?>"
            data-added-href="<?php echo esc_url( wc_get_checkout_url() ); ?>"
            class="button-canut-base button-canut-primary is-full-width ajax_add_to_cart add_to_cart_button<?php echo $already_in_cart ? ' wc-interactive' : ''; ?>"
          >
            <span data-action-label><?php echo esc_html( $already_in_cart ? __( 'Finalizar compra', 'air-light' ) : __( 'Comprar ahora', 'air-light' ) ); ?></span>
          </a>
        <?php else : ?>
          <a
            href="<?php echo esc_url( $buy_now_url ); ?>"
            class="button-canut-base button-canut-primary is-full-width"
          >
            <?php esc_html_e( 'Comprar ahora', 'air-light' ); ?>
          </a>
        <?php endif; ?>
        <a
          href="<?php echo esc_url( $whatsapp_url ); ?>"
          target="_blank"
          rel="noopener noreferrer"
          class="button-canut-base button-canut-secondary is-full-width"
        >
          <?php esc_html_e( 'Comprar por WhatsApp', 'air-light' ); ?>
        </a>

        <ul class="trust-badges-canut">
          <li class="trust-badge-canut">
            <?php require get_theme_file_path( 'assets/svg/icon-arrow-counter-clockwise.svg' ); ?>
            <span><?php esc_html_e( 'Retracto 5 días', 'air-light' ); ?></span>
          </li>
          <li class="trust-badge-canut">
            <?php require get_theme_file_path( 'assets/svg/icon-hand-coins.svg' ); ?>
            <span><?php esc_html_e( 'Pago contraentrega', 'air-light' ); ?></span>
          </li>
          <li class="trust-badge-canut">
            <?php require get_theme_file_path( 'assets/svg/icon-user.svg' ); ?>
            <span><?php esc_html_e( 'Asesoría experta', 'air-light' ); ?></span>
          </li>
          <li class="trust-badge-canut">
            <?php require get_theme_file_path( 'assets/svg/icon-truck.svg' ); ?>
            <span><?php esc_html_e( 'Envío 1-3 días', 'air-light' ); ?></span>
          </li>
        </ul>
      </div>

    </div>
  </div>

  <div class="product-gallery-lightbox" data-product-gallery-lightbox hidden>
    <button type="button" class="product-gallery-lightbox-close" data-product-gallery-lightbox-close>
      <?php require get_theme_file_path( 'assets/svg/icon-x.svg' ); ?>
      <span class="screen-reader-text"><?php esc_html_e( 'Cerrar', 'air-light' ); ?></span>
    </button>
    <button type="button" class="product-gallery-lightbox-nav is-prev" data-product-gallery-lightbox-prev>
      <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
      <span class="screen-reader-text"><?php esc_html_e( 'Foto anterior', 'air-light' ); ?></span>
    </button>
    <img class="product-gallery-lightbox-image" data-product-gallery-lightbox-image src="" alt="">
    <button type="button" class="product-gallery-lightbox-nav is-next" data-product-gallery-lightbox-next>
      <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
      <span class="screen-reader-text"><?php esc_html_e( 'Foto siguiente', 'air-light' ); ?></span>
    </button>
  </div>

  <div class="product-comparison">
    <div class="wrap-canut">
      <h2 class="product-comparison-title"><?php echo esc_html( $comparison_title ); ?></h2>

      <?php if ( $comparison_scenarios ) : ?>
        <div class="product-comparison-gallery">
          <?php foreach ( $comparison_scenarios as $scenario ) : ?>
            <div class="product-comparison-gallery-item">
              <?php if ( ! empty( $scenario['image'] ) ) : ?>
                <img src="<?php echo esc_url( $scenario['image'] ); ?>" alt="" loading="lazy">
              <?php endif; ?>
              <span class="product-comparison-gallery-caption"><?php echo esc_html( $scenario['text'] ); ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="wrap-canut product-gallery-strip">
    <h2 class="product-gallery-strip-title"><?php echo esc_html( $gallery_strip_title ); ?></h2>
    <div class="product-gallery-strip-grid">
      <?php foreach ( $gallery_strip_images as $item ) : ?>
        <div class="product-gallery-strip-item">
          <?php if ( ! empty( $item['video'] ) ) : ?>
            <video autoplay muted loop playsinline preload="metadata" poster="<?php echo esc_url( $item['image'] ); ?>" data-gallery-strip-video>
              <source src="<?php echo esc_url( $item['video'] ); ?>" <?php echo $item['video_mime'] ? 'type="' . esc_attr( $item['video_mime'] ) . '"' : ''; ?>>
            </video>
          <?php else : ?>
            <img src="<?php echo esc_url( $item['image'] ); ?>" alt="" loading="lazy">
          <?php endif; ?>
          <span class="product-gallery-strip-pill"><?php echo esc_html( $item['caption'] ); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="product-reviews">
    <div class="wrap-canut">
      <div class="product-reviews-header">
        <div>
          <h2 class="product-reviews-title"><?php echo esc_html( $reviews_title ); ?></h2>
          <?php if ( $reviews_summary ) : ?>
            <div class="product-reviews-rating">
              <span class="product-reviews-rating-score"><?php echo esc_html( $reviews_summary['score'] ); ?></span>
              <span class="product-reviews-rating-stars">
                <?php for ( $i = 0; $i < $reviews_summary['score_rounded']; $i++ ) : ?>
                  <?php require get_theme_file_path( 'assets/svg/icon-star.svg' ); ?>
                <?php endfor; ?>
              </span>
              <span class="product-reviews-rating-count"><?php echo esc_html( $reviews_summary['count'] ); ?></span>
            </div>
          <?php endif; ?>
        </div>
        <button
          type="button"
          class="button-canut-base button-canut-secondary"
          data-canut-historia-open
          data-canut-historia-product="<?php echo esc_attr( $product->get_id() ); ?>"
          data-canut-historia-product-name="<?php echo esc_attr( get_the_title() ); ?>"
        >
          <?php esc_html_e( 'Escribir reseña', 'air-light' ); ?>
        </button>
      </div>

      <?php if ( $reviews ) : ?>
        <div class="product-reviews-grid">
          <?php foreach ( $reviews as $review ) : ?>
            <div class="product-reviews-card">
              <div class="product-reviews-card-header">
                <div>
                  <p class="product-reviews-card-name"><?php echo esc_html( $review['name'] ); ?></p>
                  <p class="product-reviews-card-verified"><?php esc_html_e( 'Compra verificada', 'air-light' ); ?></p>
                </div>
                <span class="product-reviews-card-stars">
                  <?php for ( $i = 0; $i < (int) ( $review['rating'] ?? 5 ); $i++ ) : ?>
                    <?php require get_theme_file_path( 'assets/svg/icon-star.svg' ); ?>
                  <?php endfor; ?>
                </span>
              </div>
              <p class="product-reviews-card-text">&ldquo;<?php echo esc_html( $review['text'] ); ?>&rdquo;</p>
              <?php if ( ! empty( $review['image'] ) ) : ?>
                <div class="product-reviews-card-media">
                  <img src="<?php echo esc_url( $review['image'] ); ?>" alt="" loading="lazy">
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else : ?>
        <p class="product-how-it-works-step-text"><?php esc_html_e( 'Todavía no hay historias de clientes para este producto. ¡Sé el primero en compartir la tuya!', 'air-light' ); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="product-faq-extended">
    <div class="wrap-canut">
      <h2 class="product-faq-extended-title"><?php echo esc_html( $extended_faq_title ); ?></h2>
      <div class="product-faq-extended-inner" data-faq-image-sync>
        <div class="product-faq-extended-media">
          <img data-faq-image-target src="<?php echo esc_url( $extended_faq_image ); ?>" alt="" loading="lazy">
        </div>
        <div>
          <?php foreach ( $extended_faq_items as $faq ) : ?>
            <details class="accordion-canut-item" data-accordion-group="product-faq-extended" data-faq-image="<?php echo esc_url( $faq['image'] ); ?>">
              <summary class="accordion-canut-trigger">
                <?php echo esc_html( $faq['question'] ); ?>
                <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
              </summary>
              <p class="accordion-canut-panel"><?php echo esc_html( $faq['answer'] ); ?></p>
            </details>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="wrap-canut">
    <div class="product-help-cta">
      <div class="product-help-cta-inner">
        <h2 class="product-help-cta-title"><?php echo esc_html( $help_cta_title ); ?></h2>
        <p class="product-help-cta-text"><?php echo esc_html( $help_cta_text ); ?></p>
        <a href="<?php echo esc_url( $help_cta_whatsapp_url ); ?>" class="button-canut-base button-canut-light is-no-arrow" target="_blank" rel="noopener noreferrer">
          <?php require get_theme_file_path( 'assets/svg/icon-whatsapp.svg' ); ?>
          <?php esc_html_e( 'Hablar con un asesor', 'air-light' ); ?>
        </a>
      </div>
    </div>
  </div>

<?php endwhile; ?>

<?php

do_action( 'woocommerce_after_main_content' );

get_footer();
