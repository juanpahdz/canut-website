<?php
/**
 * Soporte: "Últimas preguntas" sidebar.
 *
 * Shows the 3 most recently approved community questions - Recurso_Ayuda
 * posts carrying `_soporte_submitter_name` postmeta, set only when
 * maybe_sync_pregunta_soporte_to_recurso_ayuda() (inc/hooks/soporte.php)
 * mirrors an approved Pregunta_Soporte over, so this stays scoped to
 * community-submitted answers rather than every editorial FAQ entry. Each
 * card links to the resource's own permalink (single-recurso_ayuda.php).
 *
 * @package air-light
 */

namespace Air_Light;

$hub_page = get_page_by_path( 'centro-de-ayuda' );
$hub_url  = $hub_page ? get_permalink( $hub_page ) : home_url( '/centro-de-ayuda/' );

$questions = new \WP_Query( [
  'post_type'      => 'recurso_ayuda',
  'post_status'    => 'publish',
  'posts_per_page' => 3,
  'orderby'        => 'date',
  'order'          => 'DESC',
  'meta_key'       => '_soporte_submitter_name', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- small dataset, scopes the sidebar to community-submitted answers only.
] );

if ( ! $questions->have_posts() ) {
  return;
}

// Cycles through the avatar tint variants used in the Figma design.
$avatar_variants = [ 'is-mint', 'is-peach', 'is-grey' ];

?>

<div class="soporte-recent">
  <div class="soporte-recent-heading">
    <h2><?php esc_html_e( 'Últimas preguntas', 'air-light' ); ?></h2>
    <a href="<?php echo esc_url( $hub_url ); ?>" class="soporte-recent-link"><?php esc_html_e( 'Ver todas', 'air-light' ); ?></a>
  </div>

  <div class="soporte-recent-list">
    <?php
    $i = 0;
    while ( $questions->have_posts() ) :
      $questions->the_post();
      $submitter_name = get_post_meta( get_the_ID(), '_soporte_submitter_name', true );
      $initials       = soporte_get_initials( $submitter_name );
      $variant         = $avatar_variants[ $i % count( $avatar_variants ) ];
      $i++;
      ?>
      <article class="soporte-recent-card">
        <span class="soporte-recent-avatar <?php echo esc_attr( $variant ); ?>"><?php echo esc_html( $initials ); ?></span>
        <div class="soporte-recent-content">
          <h3><?php the_title(); ?></h3>
          <p><?php echo esc_html( wp_trim_words( get_the_content(), 16 ) ); ?></p>
          <a href="<?php the_permalink(); ?>" class="soporte-recent-answer-link">
            <?php esc_html_e( 'Ver respuesta', 'air-light' ); ?>
            <?php require get_theme_file_path( 'assets/svg/icon-arrow-right.svg' ); ?>
          </a>
        </div>
      </article>
      <?php
    endwhile;
    wp_reset_postdata();
    ?>
  </div>
</div>
