<?php
/**
 * Contacto: Section 3 - WhatsApp, atención/correo y mapa (right column).
 *
 * Fed by template-parts/blocks/contacto-canut.php via $args; falls back to
 * the original CANUT design copy when included without $args.
 *
 * @package air-light
 */

namespace Air_Light;

$whatsapp_title      = $args['whatsapp_title'] ?? __( 'Atención inmediata por WhatsApp', 'air-light' );
$whatsapp_channels    = $args['whatsapp_channels'] ?? [];
$info_heading        = $args['info_heading'] ?? __( 'Atención y correo', 'air-light' );
$contact_email       = $args['contact_email'] ?? 'hola@canut.pet';
$schedule_day_label  = $args['schedule_day_label'] ?? __( 'Lunes a Viernes', 'air-light' );
$schedule_hours      = $args['schedule_hours'] ?? '8:00 am - 6:00 pm';
$address             = $args['address'] ?? '';
$map_embed_url       = $args['map_embed_url'] ?? '';
$map_link_url        = $args['map_link_url'] ?? '';

// Whitelist: channel icon comes from an ACF select field, but the value
// still ends up in a file path below, so only ever require() a known file.
$allowed_channel_icons = [ 'phone', 'headset', 'whatsapp', 'chat-circle' ];

?>

<div class="contacto-info">

  <div class="contacto-whatsapp-card">
    <div class="contacto-whatsapp-heading">
      <?php require get_theme_file_path( 'assets/svg/icon-chat-circle.svg' ); ?>
      <h3><?php echo esc_html( $whatsapp_title ); ?></h3>
    </div>

    <?php if ( $whatsapp_channels ) : ?>
      <div class="contacto-whatsapp-channels">
        <?php foreach ( $whatsapp_channels as $channel ) :
          $icon  = in_array( $channel['icon'], $allowed_channel_icons, true ) ? $channel['icon'] : 'phone';
          $phone = preg_replace( '/[^0-9]/', '', (string) $channel['phone_number'] );
          if ( ! $phone || ! $channel['label'] ) {
            continue;
          }
        ?>
          <a class="button-canut-base button-canut-secondary is-no-arrow" href="https://wa.me/<?php echo esc_attr( $phone ); ?>" target="_blank" rel="noopener noreferrer">
            <?php require get_theme_file_path( "assets/svg/icon-{$icon}.svg" ); ?>
            <?php echo esc_html( $channel['label'] ); ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="contacto-attention-card">
    <h4 class="contacto-attention-heading"><?php echo esc_html( $info_heading ); ?></h4>

    <?php if ( $contact_email ) : ?>
      <p class="contacto-attention-email">
        <?php require get_theme_file_path( 'assets/svg/icon-envelope.svg' ); ?>
        <a href="mailto:<?php echo esc_attr( $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
      </p>
    <?php endif; ?>

    <?php if ( $schedule_day_label || $schedule_hours ) : ?>
      <div class="contacto-attention-hours">
        <?php if ( $schedule_day_label ) : ?>
          <p class="contacto-attention-hours-day"><?php echo esc_html( $schedule_day_label ); ?></p>
        <?php endif; ?>
        <?php if ( $schedule_hours ) : ?>
          <p class="contacto-attention-hours-time"><?php echo esc_html( $schedule_hours ); ?></p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <?php if ( $map_embed_url ) : ?>
    <div class="contacto-map">
      <iframe
        class="contacto-map-frame"
        src="<?php echo esc_url( $map_embed_url ); ?>"
        title="<?php echo esc_attr( $address ?: __( 'Mapa de ubicación CANUT', 'air-light' ) ); ?>"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
      <?php if ( $map_link_url ) : ?>
        <a class="contacto-map-button" href="<?php echo esc_url( $map_link_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Ver en Google Maps', 'air-light' ); ?>">
          <?php require get_theme_file_path( 'assets/svg/icon-map-pin.svg' ); ?>
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

</div>
