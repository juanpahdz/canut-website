<?php
/**
 * Block: Página Contacto CANUT.
 *
 * One block renders the entire "Contacto" page. Every section below is fed
 * by the ACF fields registered in inc/acf-fields/contacto-canut.php -
 * editing the Contacto page means filling in this block's fields. Each
 * field falls back to the original CANUT design copy when left empty.
 *
 * @package air-light
 */

namespace Air_Light;

get_template_part( 'template-parts/contacto/hero', '', [
  'title' => get_field( 'hero_title' ) ?: __( 'Contacto', 'air-light' ),
  'text'  => get_field( 'hero_text' ) ?: __( 'Estamos aquí para acompañarte a ti y a tu mascota. Nuestro equipo de expertos está listo para resolver cualquier duda.', 'air-light' ),
] );

$whatsapp_channels = [];
if ( have_rows( 'whatsapp_channels' ) ) {
  while ( have_rows( 'whatsapp_channels' ) ) {
    the_row();
    $whatsapp_channels[] = [
      'icon'         => get_sub_field( 'icon' ) ?: 'phone',
      'label'        => get_sub_field( 'label' ),
      'phone_number' => get_sub_field( 'phone_number' ),
    ];
  }
}
if ( ! $whatsapp_channels ) {
  $whatsapp_channels = [
    [ 'icon' => 'phone', 'label' => __( 'Canal Ventas', 'air-light' ), 'phone_number' => '573000000000' ],
    [ 'icon' => 'headset', 'label' => __( 'Soporte Técnico', 'air-light' ), 'phone_number' => '573000000000' ],
  ];
}

?>

<section class="block contacto-canut">
  <div class="wrap-canut contacto-canut-inner">

    <div class="contacto-canut-columns">

      <?php get_template_part( 'template-parts/contacto/form', '', [
        'title'           => get_field( 'form_title' ) ?: __( 'Envíanos un mensaje', 'air-light' ),
        'button_label'    => get_field( 'form_button_label' ) ?: __( 'Enviar mensaje', 'air-light' ),
        'success_message' => get_field( 'form_success_message' ) ?: __( '¡Gracias! Tu mensaje fue enviado, te responderemos pronto.', 'air-light' ),
        'error_message'   => get_field( 'form_error_message' ) ?: __( 'Ocurrió un error al enviar tu mensaje. Intenta de nuevo o escríbenos por WhatsApp.', 'air-light' ),
        'recipient_email' => get_field( 'form_recipient_email' ) ?: get_option( 'admin_email' ),
      ] ); ?>

      <?php get_template_part( 'template-parts/contacto/info', '', [
        'whatsapp_title'      => get_field( 'whatsapp_title' ) ?: __( 'Atención inmediata por WhatsApp', 'air-light' ),
        'whatsapp_channels'   => $whatsapp_channels,
        'info_heading'        => get_field( 'info_heading' ) ?: __( 'Atención y correo', 'air-light' ),
        'contact_email'       => get_field( 'contact_email' ) ?: 'hola@canut.pet',
        'schedule_day_label'  => get_field( 'schedule_day_label' ) ?: __( 'Lunes a Viernes', 'air-light' ),
        'schedule_hours'      => get_field( 'schedule_hours' ) ?: '8:00 am - 6:00 pm',
        'address'             => get_field( 'address' ) ?: '',
        'map_embed_url'       => get_field( 'map_embed_url' ) ?: '',
        'map_link_url'        => get_field( 'map_link_url' ) ?: '',
      ] ); ?>

    </div>

  </div>
</section>
