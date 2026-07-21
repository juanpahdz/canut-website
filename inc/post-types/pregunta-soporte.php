<?php
/**
 * The Pregunta de soporte post type class.
 *
 * One post = one community question submitted via page-soporte.php's
 * "Crea tu pregunta" form. Starts as post_status `pending` (title =
 * pregunta submitted by a visitor). An editor reviews it in wp-admin,
 * writes the answer into the native editor, and clicking "Publicar" is
 * the approval step: inc/hooks/soporte.php mirrors it into a new
 * Recurso_Ayuda post (the Centro de ayuda FAQ listing), tagged with the
 * same categoria_ayuda term the visitor picked as "Tema".
 *
 * No public single/archive template exists for this post type - it's an
 * internal moderation queue, not a front-end destination of its own.
 *
 * @package air-light
 **/

namespace Air_Light;

/**
 * Registers the Pregunta de soporte post type.
 */
class Pregunta_Soporte extends Post_Type {

  public function register() {
    $generated_labels = [
      'menu_name'          => self::ask__( 'Pregunta soporte menu name', 'Preguntas de soporte' ),
      'name'               => self::ask__( 'Pregunta soporte name', 'Preguntas de soporte' ),
      'singular_name'      => self::ask__( 'Pregunta soporte singular name', 'Pregunta de soporte' ),
      'name_admin_bar'     => self::ask__( 'Pregunta soporte name admin bar', 'Pregunta de soporte' ),
      'add_new'            => self::ask__( 'Pregunta soporte add new', 'Añadir nueva' ),
      'add_new_item'       => self::ask__( 'Pregunta soporte add new item', 'Añadir nueva pregunta de soporte' ),
      'new_item'           => self::ask__( 'Pregunta soporte new item', 'Nueva pregunta de soporte' ),
      'edit_item'          => self::ask__( 'Pregunta soporte edit item', 'Revisar pregunta de soporte' ),
      'view_item'          => self::ask__( 'Pregunta soporte view item', 'Ver pregunta de soporte' ),
      'all_items'          => self::ask__( 'Pregunta soporte all items', 'Todas las preguntas' ),
      'search_items'       => self::ask__( 'Pregunta soporte search items', 'Buscar preguntas de soporte' ),
      'parent_item_colon'  => self::ask__( 'Pregunta soporte parent item colon', 'Pregunta superior:' ),
      'not_found'          => self::ask__( 'Pregunta soporte not found', 'No se encontraron preguntas de soporte.' ),
      'not_found_in_trash' => self::ask__( 'Pregunta soporte not found in trash', 'No se encontraron preguntas de soporte en la papelera.' ),
    ];

    $args = [
      'labels'              => $generated_labels,
      'menu_icon'           => 'dashicons-format-chat',
      // Internal moderation queue only - no single/archive front-end view,
      // approved questions live on as Recurso_Ayuda posts instead.
      'public'              => false,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'has_archive'         => false,
      'exclude_from_search' => true,
      'show_in_rest'        => true,
      'pll_translatable'    => false,
      'capability_type'     => 'post',
      // Title = pregunta (set by the front-end form), native editor =
      // respuesta (filled in by the reviewing editor before publishing).
      'supports'            => [ 'title', 'editor', 'revisions' ],
      'taxonomies'          => [ 'categoria_ayuda' ],
    ];

    $this->register_wp_post_type( $this->slug, $args );
  }
}
