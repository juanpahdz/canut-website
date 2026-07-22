<?php
/**
 * Template Name: Página informativa
 *
 * Reusable template for legal/support pages (Términos de Servicio, Política
 * de Privacidad, Shipping Policy, Warranty, etc.). Content is a repeater of
 * sections (title + WYSIWYG, field "secciones") so editors can add as many
 * sections as a given page needs; numbering and the table-of-contents links
 * are generated from that repeater, not hardcoded.
 *
 * @package air-light
 */

namespace Air_Light;

the_post();

get_header();

$secciones      = get_field( 'secciones' ) ?: [];
$subtitulo      = get_field( 'subtitulo' );
$etiqueta_migas = get_field( 'etiqueta_migas' );

// Built once so the TOC links (pass 1) and the section headings (pass 2)
// share the exact same number and anchor for a given row.
$toc = [];
foreach ( $secciones as $index => $seccion ) {
  $toc[] = [
    'number' => $index + 1,
    'title'  => $seccion['titulo'],
    'anchor' => sanitize_title( ( $index + 1 ) . '-' . $seccion['titulo'] ),
  ];
}
?>

<main class="site-main informational-page">
  <div class="wrap-canut informational-page-wrap">

    <header class="informational-page-hero">
      <nav class="informational-page-breadcrumb" aria-label="<?php esc_attr_e( 'Migas de pan', 'air-light' ); ?>">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Inicio', 'air-light' ); ?></a>
        <?php if ( $etiqueta_migas ) : ?>
          <?php require get_theme_file_path( 'assets/svg/icon-chevron-down.svg' ); ?>
          <span><?php echo esc_html( $etiqueta_migas ); ?></span>
        <?php endif; ?>
      </nav>

      <h1 class="informational-page-title"><?php the_title(); ?></h1>

      <?php if ( $subtitulo ) : ?>
        <p class="informational-page-subtitle"><?php echo esc_html( $subtitulo ); ?></p>
      <?php endif; ?>
    </header>

    <div class="informational-page-layout">

      <?php if ( count( $toc ) > 1 ) : ?>
        <aside class="informational-page-toc">
          <p class="informational-page-toc-heading"><?php esc_html_e( 'Contenido', 'air-light' ); ?></p>
          <ul class="informational-page-toc-list">
            <?php foreach ( $toc as $item ) : ?>
              <li>
                <a class="informational-page-toc-link" href="#<?php echo esc_attr( $item['anchor'] ); ?>">
                  <?php echo esc_html( $item['number'] . '. ' . $item['title'] ); ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </aside>
      <?php endif; ?>

      <article class="informational-page-content">
        <?php foreach ( $secciones as $index => $seccion ) : ?>
          <section id="<?php echo esc_attr( $toc[ $index ]['anchor'] ); ?>" class="informational-page-section">
            <h2 class="informational-page-section-title">
              <?php echo esc_html( $toc[ $index ]['number'] . '. ' . $toc[ $index ]['title'] ); ?>
            </h2>
            <div class="informational-page-section-body">
              <?php echo wp_kses_post( $seccion['contenido'] ); ?>
            </div>
          </section>
        <?php endforeach; ?>

        <?php air_edit_link(); ?>
      </article>

    </div>

  </div>
</main>

<?php get_footer();
