<?php
/**
 * Soporte: "Preguntas frecuentes" accordion.
 *
 * Static, general store FAQs (warranty/waterproofing/delivery) from the
 * original Figma design (file kt0CQUzjzqKYQOklRo72EA, node 87:612) - not
 * tied to the Pregunta_Soporte/Recurso_Ayuda workflow above, same
 * treatment as other static Figma-sourced copy blocks elsewhere in the
 * theme. Reuses the existing accordion-canut component.
 *
 * @package air-light
 */

namespace Air_Light;

$items = [
  [
    'question' => __( '¿Cómo funciona la garantía vitalicia?', 'air-light' ),
    'answer'   => __( 'Todos los productos CANUT incluyen una garantía vitalicia contra defectos de fabricación. Si algo falla por un problema de manufactura, lo reparamos o reemplazamos sin costo.', 'air-light' ),
  ],
  [
    'question' => __( '¿Los productos son resistentes al agua?', 'air-light' ),
    'answer'   => __( 'Sí, la tecnología CANUT está sellada para resistir lluvia, salpicaduras y la humedad del uso diario al aire libre, aunque no está diseñada para inmersión prolongada.', 'air-light' ),
  ],
  [
    'question' => __( '¿Cuál es el tiempo de entrega estimado?', 'air-light' ),
    'answer'   => __( 'Los pedidos locales suelen llegar entre 2 y 5 días hábiles. Te enviamos el número de seguimiento apenas tu pedido sale de nuestro taller.', 'air-light' ),
  ],
];

?>

<section class="soporte-faq">
  <div class="wrap-canut soporte-faq-inner">
    <h2 class="soporte-faq-title"><?php esc_html_e( 'Preguntas frecuentes', 'air-light' ); ?></h2>

    <div class="soporte-faq-list">
      <?php foreach ( $items as $item ) : ?>
        <details class="accordion-canut-item">
          <summary class="accordion-canut-trigger">
            <span><?php echo esc_html( $item['question'] ); ?></span>
            <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
          </summary>
          <div class="accordion-canut-panel">
            <p><?php echo esc_html( $item['answer'] ); ?></p>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
