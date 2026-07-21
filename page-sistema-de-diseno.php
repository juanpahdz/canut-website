<?php
/**
 * Template for the internal design-system reference page.
 *
 * Applies automatically to the WordPress Page with slug `sistema-de-diseno`
 * (WP template hierarchy: page-{slug}.php). Not linked from any navigation;
 * noindex/robots/sitemap exclusion is handled in inc/hooks/design-system.php.
 *
 * @package air-light
 */

namespace Air_Light;

the_post();

get_header();

$colors = [
  [ 'name' => 'Primary',         'slug' => 'primary',        'value' => '#223D2E' ],
  [ 'name' => 'Accent',          'slug' => 'accent',         'value' => '#B16B3A' ],
  [ 'name' => 'Primary dark',    'slug' => 'primary-dark',   'value' => '#1A2F23' ],
  [ 'name' => 'Accent dark',     'slug' => 'accent-dark',    'value' => '#8F5228' ],
  [ 'name' => 'Background base', 'slug' => 'bg-base',        'value' => '#FAF8F5' ],
  [ 'name' => 'Background surface', 'slug' => 'bg-surface',  'value' => '#FFFFFF' ],
  [ 'name' => 'Text primary',    'slug' => 'text-primary',   'value' => '#223D2E' ],
  [ 'name' => 'Text secondary',  'slug' => 'text-secondary', 'value' => '#5C6B62' ],
  [ 'name' => 'Border',          'slug' => 'border',         'value' => '#E4DFD8' ],
  [ 'name' => 'Success',         'slug' => 'success',        'value' => '#4A7A5E' ],
  [ 'name' => 'Error',           'slug' => 'error',          'value' => '#B4483C' ],
];

$type_scale = [
  [ 'token' => 'h-1',     'family' => 'display', 'weight' => 'medium',  'label' => 'H1 · 40/32px · peso 500', 'sample' => 'CANUT nace del respeto por el proceso.' ],
  [ 'token' => 'h-2',     'family' => 'display', 'weight' => 'regular', 'label' => 'H2 · 32/26px · peso 400', 'sample' => 'Cada pieza cuenta una historia.' ],
  [ 'token' => 'h-3',     'family' => 'display', 'weight' => 'medium',  'label' => 'H3 · 24/20px · peso 500', 'sample' => 'Hecho para acompañarte todos los días.' ],
  [ 'token' => 'h-4',     'family' => 'display', 'weight' => 'medium',  'label' => 'H4 · 20/18px · peso 500', 'sample' => 'Materiales que envejecen con dignidad.' ],
];

$body_scale = [
  [ 'token' => 'body-lg', 'family' => 'body', 'weight' => 'regular', 'label' => 'Body LG · 20/18px · peso 400', 'sample' => 'En CANUT creemos que lo esencial no necesita adornos.' ],
  [ 'token' => 'body',    'family' => 'body', 'weight' => 'regular', 'label' => 'Body · 18/16px · peso 400', 'sample' => 'Diseñamos productos pensados para durar, no para reemplazarse.' ],
  [ 'token' => 'small',   'family' => 'body', 'weight' => 'regular', 'label' => 'Small · 15/14px · peso 400', 'sample' => 'Envío disponible a todo el país en 1-3 días hábiles.' ],
  [ 'token' => 'caption', 'family' => 'body', 'weight' => 'medium',  'label' => 'Caption · 12/11px · peso 500, uppercase, tracking .04em', 'sample' => 'Edición limitada' ],
];

$spacing_tokens = [
  [ 'token' => 'space-xs',  'px' => 8 ],
  [ 'token' => 'space-sm',  'px' => 16 ],
  [ 'token' => 'space-md',  'px' => 24 ],
  [ 'token' => 'space-lg',  'px' => 48 ],
  [ 'token' => 'space-xl',  'px' => 96 ],
  [ 'token' => 'space-2xl', 'px' => 128 ],
];

$radii = [
  [ 'token' => 'btn',  'label' => '$radius-btn · 6px', 'note' => 'Botones' ],
  [ 'token' => 'sm',   'label' => '$radius-sm · 8px', 'note' => 'Inputs, badges pequeños' ],
  [ 'token' => 'md',   'label' => '$radius-md · 16px', 'note' => 'Cards' ],
  [ 'token' => 'lg',   'label' => '$radius-lg · 24px', 'note' => 'Imágenes grandes' ],
  [ 'token' => 'full', 'label' => '$radius-full · 999px', 'note' => 'Pills, avatares — nunca botones' ],
];

$nav_sections = [
  'color'         => 'Color',
  'tipografia'    => 'Tipografía',
  'blog'          => 'Blog / contenido',
  'espaciado'     => 'Espaciado',
  'radios'        => 'Radios y sombras',
  'botones'       => 'Botones',
  'formularios'   => 'Formularios',
  'metodo-pago'   => 'Método de pago',
  'banners'       => 'Banners',
];
?>

<main class="site-main has-global-padding design-system-canut">

  <nav class="design-system-canut-nav" aria-label="<?php esc_attr_e( 'Secciones del sistema de diseño', 'air-light' ); ?>">
    <ul>
      <?php foreach ( $nav_sections as $anchor => $label ) : ?>
        <li><a href="#<?php echo esc_attr( $anchor ); ?>"><?php echo esc_html( $label ); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <div class="design-system-canut-content">

    <p class="design-system-canut-eyebrow is-demo-tight">
      <?php echo esc_html__( 'Uso interno — diseño / desarrollo', 'air-light' ); ?>
    </p>
    <h1 class="design-system-canut-title" style="font-size:var(--wp--preset--font-size--h-1);line-height:var(--wp--custom--canut--line-height--h-1);">
      <?php echo esc_html__( 'Sistema de diseño CANUT', 'air-light' ); ?>
    </h1>

    <!-- 1. COLOR -->
    <section id="color" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Tokens', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Color', 'air-light' ); ?></h2>

      <div class="design-system-canut-swatches">
        <?php foreach ( $colors as $color ) : ?>
          <div class="design-system-canut-swatch">
            <div class="design-system-canut-swatch-color" style="background-color:var(--wp--preset--color--<?php echo esc_attr( $color['slug'] ); ?>);"></div>
            <div class="design-system-canut-swatch-meta">
              <span class="design-system-canut-swatch-name"><?php echo esc_html( $color['name'] ); ?></span>
              <span class="design-system-canut-swatch-value">$color-<?php echo esc_html( $color['slug'] ); ?>: <?php echo esc_html( $color['value'] ); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- 2. TIPOGRAFIA -->
    <section id="tipografia" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Tokens', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Tipografía', 'air-light' ); ?></h2>

      <div class="design-system-canut-grid-2">
        <div>
          <span class="design-system-canut-eyebrow">Manrope — font-display</span>
          <?php foreach ( $type_scale as $type ) : ?>
            <div class="design-system-canut-type-sample">
              <span class="design-system-canut-type-label"><?php echo esc_html( $type['label'] ); ?></span>
              <p style="font-family:var(--wp--preset--font-family--<?php echo esc_attr( $type['family'] ); ?>);font-size:var(--wp--preset--font-size--<?php echo esc_attr( $type['token'] ); ?>);font-weight:var(--wp--custom--font-weight--<?php echo esc_attr( $type['weight'] ); ?>);line-height:var(--wp--custom--canut--line-height--<?php echo esc_attr( $type['token'] ); ?>);">
                <?php echo esc_html( $type['sample'] ); ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>

        <div>
          <span class="design-system-canut-eyebrow">Inter — font-body</span>
          <?php foreach ( $body_scale as $type ) : ?>
            <div class="design-system-canut-type-sample">
              <span class="design-system-canut-type-label"><?php echo esc_html( $type['label'] ); ?></span>
              <p style="<?php echo 'caption' === $type['token'] ? 'text-transform:uppercase;letter-spacing:var(--wp--custom--canut--letter-spacing--caption);' : ''; ?>font-family:var(--wp--preset--font-family--<?php echo esc_attr( $type['family'] ); ?>);font-size:var(--wp--preset--font-size--<?php echo esc_attr( $type['token'] ); ?>);font-weight:var(--wp--custom--font-weight--<?php echo esc_attr( $type['weight'] ); ?>);line-height:var(--wp--custom--canut--line-height--<?php echo esc_attr( $type['token'] ); ?>);">
                <?php echo esc_html( $type['sample'] ); ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- 3. BLOG / CONTENIDO -->
    <section id="blog" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Vista previa', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Blog / contenido', 'air-light' ); ?></h2>
      <p class="design-system-canut-type-label" style="margin-bottom:var(--wp--preset--spacing--space-md);">
        <?php echo esc_html__( 'Títulos, párrafos, listas y tablas tal como aparecerían juntos dentro de una entrada real, para revisar que la jerarquía y el espaciado se lean bien en conjunto.', 'air-light' ); ?>
      </p>

      <article class="design-system-canut-prose">
        <h1><?php echo esc_html__( 'Cómo cuidamos cada pieza CANUT', 'air-light' ); ?></h1>
        <p class="design-system-canut-prose-lead">
          <?php echo esc_html__( 'Cada producto CANUT pasa por un proceso artesanal pensado para que envejezca con dignidad, no para que se reemplace.', 'air-light' ); ?>
        </p>
        <p>
          <?php echo esc_html__( 'Trabajamos con maestros carpinteros que seleccionan la madera pieza por pieza, respetando su veta natural y su tiempo de secado.', 'air-light' ); ?>
        </p>

        <h2><?php echo esc_html__( 'Nuestro proceso', 'air-light' ); ?></h2>
        <p>
          <?php echo esc_html__( 'Desde la selección de la materia prima hasta el empaque final, cada paso ocurre en nuestro taller.', 'air-light' ); ?>
        </p>
        <ul>
          <li><?php echo esc_html__( 'Selección de madera certificada de bosques renovables', 'air-light' ); ?></li>
          <li><?php echo esc_html__( 'Secado natural durante 6 semanas', 'air-light' ); ?></li>
          <li><?php echo esc_html__( 'Tallado y lijado a mano', 'air-light' ); ?></li>
          <li><?php echo esc_html__( 'Acabado en aceite natural, libre de químicos agresivos', 'air-light' ); ?></li>
        </ul>

        <h3><?php echo esc_html__( 'Cuidados diarios', 'air-light' ); ?></h3>
        <p>
          <?php echo esc_html__( 'Con un mantenimiento sencillo, tus piezas CANUT se mantienen impecables por años.', 'air-light' ); ?>
        </p>
        <ol>
          <li><?php echo esc_html__( 'Limpia con un paño húmedo después de cada uso', 'air-light' ); ?></li>
          <li><?php echo esc_html__( 'Seca de inmediato, evita dejarla al aire libre', 'air-light' ); ?></li>
          <li><?php echo esc_html__( 'Aplica aceite mineral cada 2-3 meses', 'air-light' ); ?></li>
          <li><?php echo esc_html__( 'Evita el lavavajillas y el microondas', 'air-light' ); ?></li>
        </ol>

        <h4><?php echo esc_html__( 'Comparativa de acabados', 'air-light' ); ?></h4>
        <p>
          <?php echo esc_html__( 'Elige el acabado según el uso que le darás a la pieza.', 'air-light' ); ?>
        </p>
        <table>
          <thead>
            <tr>
              <th scope="col"><?php echo esc_html__( 'Acabado', 'air-light' ); ?></th>
              <th scope="col"><?php echo esc_html__( 'Resistencia al agua', 'air-light' ); ?></th>
              <th scope="col"><?php echo esc_html__( 'Mantenimiento', 'air-light' ); ?></th>
              <th scope="col"><?php echo esc_html__( 'Recomendado para', 'air-light' ); ?></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?php echo esc_html__( 'Aceite natural', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Media', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Cada 2-3 meses', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Uso diario', 'air-light' ); ?></td>
            </tr>
            <tr>
              <td><?php echo esc_html__( 'Barniz mate', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Alta', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Anual', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Piezas decorativas', 'air-light' ); ?></td>
            </tr>
            <tr>
              <td><?php echo esc_html__( 'Cera de abejas', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Baja', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Mensual', 'air-light' ); ?></td>
              <td><?php echo esc_html__( 'Utensilios de cocina', 'air-light' ); ?></td>
            </tr>
          </tbody>
        </table>

        <p class="design-system-canut-prose-note">
          <?php echo esc_html__( '¿Tienes dudas sobre el cuidado de tu pieza? Escríbenos y te asesoramos.', 'air-light' ); ?>
        </p>
      </article>
    </section>

    <!-- 4. ESPACIADO -->
    <section id="espaciado" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Tokens', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Espaciado', 'air-light' ); ?></h2>

      <?php foreach ( $spacing_tokens as $space ) : ?>
        <div class="design-system-canut-spacing-row">
          <span class="design-system-canut-spacing-label">$<?php echo esc_html( $space['token'] ); ?></span>
          <div class="design-system-canut-spacing-bar" style="width:var(--wp--preset--spacing--<?php echo esc_attr( $space['token'] ); ?>);"></div>
          <span class="design-system-canut-spacing-label"><?php echo esc_html( $space['px'] ); ?>px</span>
        </div>
      <?php endforeach; ?>
    </section>

    <!-- 5. RADIOS Y SOMBRAS -->
    <section id="radios" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Tokens', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Radios y sombras', 'air-light' ); ?></h2>

      <div class="design-system-canut-demo-grid">
        <?php foreach ( $radii as $radius ) : ?>
          <div class="design-system-canut-demo-box" style="border-radius:var(--wp--custom--canut--radius--<?php echo esc_attr( $radius['token'] ); ?>);">
            <?php echo esc_html( $radius['label'] ); ?>
          </div>
        <?php endforeach; ?>

        <div class="design-system-canut-demo-box" style="border-radius:var(--wp--custom--canut--radius--md);box-shadow:var(--wp--preset--shadow--card);">
          $shadow-card
        </div>
        <div class="design-system-canut-demo-box" style="border-radius:var(--wp--custom--canut--radius--md);box-shadow:var(--wp--preset--shadow--hover);">
          $shadow-hover
        </div>
      </div>
    </section>

    <!-- 6. BOTONES -->
    <section id="botones" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Componente', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Botones', 'air-light' ); ?></h2>

      <p class="design-system-canut-prose-note">
        <?php echo esc_html__( 'Por defecto, al hacer hover/focus aparece una flecha con una animación smooth; añade is-no-arrow para quitarla. Por defecto el botón se ajusta a su texto; añade is-full-width para ocupar todo el ancho disponible. Un ícono a la izquierda funciona con solo colocar un <svg> antes del texto. La clase is-loading muestra un spinner y bloquea el botón; usa el helper withButtonCanutLoading (modules/button-canut.js) para que un segundo clic no dispare la acción dos veces.', 'air-light' ); ?>
      </p>

      <div class="design-system-canut-row">
        <button type="button" class="button-canut-base button-canut-primary"><?php echo esc_html__( 'Comprar ahora', 'air-light' ); ?></button>
        <button type="button" class="button-canut-base button-canut-secondary"><?php echo esc_html__( 'Ver detalles', 'air-light' ); ?></button>
        <button type="button" class="button-canut-base button-canut-secondary is-no-arrow"><?php echo esc_html__( 'Sin flecha (is-no-arrow)', 'air-light' ); ?></button>
      </div>

      <div class="design-system-canut-row">
        <button type="button" class="button-canut-base button-canut-primary" data-canut-loading-demo>
          <?php require get_theme_file_path( 'assets/svg/icon-check.svg' ); ?>
          <?php echo esc_html__( 'Ícono a la izquierda + loading (clic aquí)', 'air-light' ); ?>
        </button>
      </div>

      <div class="design-system-canut-row design-system-canut-demo-frame">
        <button type="button" class="button-canut-base button-canut-checkout is-full-width">
          <?php require get_theme_file_path( 'assets/svg/icon-lock.svg' ); ?>
          <?php echo esc_html__( 'Finalizar compra', 'air-light' ); ?>
        </button>
      </div>
    </section>

    <!-- 7. FORMULARIOS -->
    <section id="formularios" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Componente', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Formularios', 'air-light' ); ?></h2>

      <p class="design-system-canut-prose-note">
        <?php echo esc_html__( '.input-canut, .select-canut (envuelto en .select-canut-wrap para el chevron) y .textarea-canut. Usado en checkout a través de woocommerce_form_field() - ver .form-canut-group en inc/hooks/checkout.php, que aplica el mismo estilo automáticamente a la etiqueta y al campo real de WooCommerce sin necesitar input_class/label_class por campo.', 'air-light' ); ?>
      </p>

      <div class="design-system-canut-grid-2">
        <div class="form-canut-group">
          <label class="form-canut-label" for="demo-nombre"><?php echo esc_html__( 'Nombre completo', 'air-light' ); ?></label>
          <input class="input-canut" type="text" id="demo-nombre" placeholder="<?php esc_attr_e( 'Tu nombre y apellido', 'air-light' ); ?>">
        </div>

        <div class="form-canut-group">
          <label class="form-canut-label" for="demo-telefono">
            <?php echo esc_html__( 'Teléfono / WhatsApp', 'air-light' ); ?>
            <span class="form-canut-tooltip">
              <button type="button" class="form-canut-tooltip-trigger" aria-describedby="demo-telefono-tooltip">
                <?php require get_theme_file_path( 'assets/svg/icon-info.svg' ); ?>
              </button>
              <span class="form-canut-tooltip-bubble" id="demo-telefono-tooltip" role="tooltip">
                <?php echo esc_html__( 'Te contactamos para confirmar tu pedido antes de enviarlo', 'air-light' ); ?>
              </span>
            </span>
          </label>
          <input class="input-canut" type="tel" id="demo-telefono" placeholder="300 000 0000">
        </div>

        <div class="form-canut-group">
          <label class="form-canut-label" for="demo-ciudad"><?php echo esc_html__( 'Ciudad (Solo Área Metropolitana)', 'air-light' ); ?></label>
          <div class="select-canut-wrap">
            <select class="select-canut" id="demo-ciudad">
              <option><?php echo esc_html__( 'Medellín', 'air-light' ); ?></option>
            </select>
          </div>
        </div>

        <div class="form-canut-group">
          <label class="form-canut-label" for="demo-indicaciones"><?php echo esc_html__( 'Indicaciones adicionales (Opcional)', 'air-light' ); ?></label>
          <textarea class="textarea-canut" id="demo-indicaciones" placeholder="<?php esc_attr_e( 'Torre 2, Portería principal, etc.', 'air-light' ); ?>"></textarea>
        </div>
      </div>
    </section>

    <!-- 8. METODO DE PAGO -->
    <section id="metodo-pago" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Componente', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Método de pago', 'air-light' ); ?></h2>

      <p class="design-system-canut-prose-note">
        <?php echo esc_html__( '.payment-option-canut envuelve un <input type=radio> real (ver woocommerce/checkout/payment-method.php) - la tarjeta completa es clicable porque es un <label>, y el estado seleccionado usa :has(:checked), sin JS.', 'air-light' ); ?>
      </p>

      <div class="checkout-canut-payment-methods design-system-canut-demo-frame">
        <label class="payment-option-canut">
          <span class="payment-option-canut-top">
            <?php require get_theme_file_path( 'assets/svg/icon-hand-coins.svg' ); ?>
            <input type="radio" class="payment-option-canut-input" name="demo_payment_method" checked>
          </span>
          <span class="payment-option-canut-body">
            <span class="payment-option-canut-title-row">
              <span class="payment-option-canut-title"><?php echo esc_html__( 'Pago contraentrega', 'air-light' ); ?></span>
            </span>
            <span class="payment-option-canut-desc"><?php echo esc_html__( 'Paga en efectivo o datáfono al recibir tu pedido.', 'air-light' ); ?></span>
          </span>
        </label>

        <label class="payment-option-canut">
          <span class="payment-option-canut-top">
            <?php require get_theme_file_path( 'assets/svg/icon-credit-card.svg' ); ?>
            <input type="radio" class="payment-option-canut-input" name="demo_payment_method">
          </span>
          <span class="payment-option-canut-body">
            <span class="payment-option-canut-title-row">
              <span class="payment-option-canut-title"><?php echo esc_html__( 'Pago en línea', 'air-light' ); ?></span>
              <span class="badge-canut is-dark"><?php echo esc_html__( '8% OFF', 'air-light' ); ?></span>
            </span>
            <span class="payment-option-canut-desc"><?php echo esc_html__( 'Tarjeta de crédito, PSE o Nequi.', 'air-light' ); ?></span>
          </span>
        </label>
      </div>
    </section>

    <!-- 9. BANNERS -->
    <section id="banners" class="design-system-canut-section">
      <span class="design-system-canut-eyebrow"><?php echo esc_html__( 'Componente', 'air-light' ); ?></span>
      <h2 class="design-system-canut-title"><?php echo esc_html__( 'Banners', 'air-light' ); ?></h2>

      <div class="design-system-canut-demo-frame design-system-canut-stack">
        <div class="banner-canut is-mint">
          <?php require get_theme_file_path( 'assets/svg/icon-truck.svg' ); ?>
          <div>
            <p class="banner-canut-title"><?php echo esc_html__( 'Tu pedido será enviado con la transportadora con mejor cobertura.', 'air-light' ); ?></p>
            <p class="banner-canut-text"><?php echo esc_html__( '(Interrapidísimo, Servientrega o Coordinadora). Tiempo: 1-3 días.', 'air-light' ); ?></p>
          </div>
        </div>

        <div class="banner-canut is-help">
          <div class="banner-canut-content">
            <?php require get_theme_file_path( 'assets/svg/icon-chat-circle.svg' ); ?>
            <p class="banner-canut-title"><?php echo esc_html__( '¿Dudas con tu pedido? Escríbenos', 'air-light' ); ?></p>
          </div>
          <a class="banner-canut-link" href="https://wa.me/"><?php echo esc_html__( 'Contactar por WhatsApp', 'air-light' ); ?></a>
        </div>
      </div>
    </section>

  </div>
</main>

<?php get_footer();
