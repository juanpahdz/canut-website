<?php
/**
 * The Recurso de ayuda post type class.
 *
 * One post = one help-center FAQ entry. Title is the question, the native
 * editor (WYSIWYG) is the answer, grouped into categoria_ayuda terms and
 * rendered on the Centro de ayuda hub (page-centro-de-ayuda.php) plus its
 * own permalink (single-recurso_ayuda.php).
 *
 * @package air-light
 **/

namespace Air_Light;

/**
 * Registers the Recurso de ayuda post type.
 */
class Recurso_Ayuda extends Post_Type {

  public function register() {
    $generated_labels = [
      'menu_name'          => self::ask__( 'Recurso ayuda menu name', 'Centro de ayuda' ),
      'name'               => self::ask__( 'Recurso ayuda name', 'Recursos de ayuda' ),
      'singular_name'      => self::ask__( 'Recurso ayuda singular name', 'Recurso de ayuda' ),
      'name_admin_bar'     => self::ask__( 'Recurso ayuda name admin bar', 'Recurso de ayuda' ),
      'add_new'            => self::ask__( 'Recurso ayuda add new', 'Añadir nuevo' ),
      'add_new_item'       => self::ask__( 'Recurso ayuda add new item', 'Añadir nuevo recurso de ayuda' ),
      'new_item'           => self::ask__( 'Recurso ayuda new item', 'Nuevo recurso de ayuda' ),
      'edit_item'          => self::ask__( 'Recurso ayuda edit item', 'Editar recurso de ayuda' ),
      'view_item'          => self::ask__( 'Recurso ayuda view item', 'Ver recurso de ayuda' ),
      'all_items'          => self::ask__( 'Recurso ayuda all items', 'Todos los recursos' ),
      'search_items'       => self::ask__( 'Recurso ayuda search items', 'Buscar recursos de ayuda' ),
      'parent_item_colon'  => self::ask__( 'Recurso ayuda parent item colon', 'Recurso superior:' ),
      'not_found'          => self::ask__( 'Recurso ayuda not found', 'No se encontraron recursos de ayuda.' ),
      'not_found_in_trash' => self::ask__( 'Recurso ayuda not found in trash', 'No se encontraron recursos de ayuda en la papelera.' ),
    ];

    $args = [
      'labels'              => $generated_labels,
      'menu_icon'           => 'dashicons-editor-help',
      'public'              => true,
      'show_ui'             => true,
      // No native archive - the Centro de ayuda page (page-centro-de-ayuda.php)
      // is the canonical listing, grouped by categoria_ayuda instead of a
      // flat chronological archive.
      'has_archive'         => false,
      'exclude_from_search' => false,
      'show_in_rest'        => true,
      'pll_translatable'    => true,
      'rewrite'             => [
        'with_front' => false,
        'slug'       => 'recurso-ayuda',
      ],
      // page-attributes gives editors manual drag-order (menu_order) within
      // a category, same convention as the Historia post type.
      'supports'            => [ 'title', 'editor', 'page-attributes', 'revisions' ],
      'taxonomies'          => [ 'categoria_ayuda' ],
    ];

    $this->register_wp_post_type( $this->slug, $args );
  }
}
