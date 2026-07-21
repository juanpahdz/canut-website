<?php
/**
 * The Historia post type class.
 *
 * @package air-light
 **/

namespace Air_Light;

/**
 * Registers the Historia post type.
 */
class Historia extends Post_Type {

  public function register() {
    $generated_labels = [
      'menu_name'          => self::ask__( 'Historia menu name', 'Historias' ),
      'name'               => self::ask__( 'Historia name', 'Historias' ),
      'singular_name'      => self::ask__( 'Historia singular name', 'Historia' ),
      'name_admin_bar'     => self::ask__( 'Historia name admin bar', 'Historia' ),
      'add_new'            => self::ask__( 'Historia add new', 'Añadir nueva' ),
      'add_new_item'       => self::ask__( 'Historia add new item', 'Añadir nueva historia' ),
      'new_item'           => self::ask__( 'Historia new item', 'Nueva historia' ),
      'edit_item'          => self::ask__( 'Historia edit item', 'Editar historia' ),
      'view_item'          => self::ask__( 'Historia view item', 'Ver historia' ),
      'all_items'          => self::ask__( 'Historia all items', 'Todas las historias' ),
      'search_items'       => self::ask__( 'Historia search items', 'Buscar historias' ),
      'parent_item_colon'  => self::ask__( 'Historia parent item colon', 'Historia superior:' ),
      'not_found'          => self::ask__( 'Historia not found', 'No se encontraron historias.' ),
      'not_found_in_trash' => self::ask__( 'Historia not found in trash', 'No se encontraron historias en la papelera.' ),
    ];

    $args = [
      'labels'              => $generated_labels,
      'menu_icon'           => 'dashicons-format-quote',
      'public'              => true,
      'show_ui'             => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'show_in_rest'        => false,
      'pll_translatable'    => true,
      'rewrite'             => [
        'with_front'  => false,
        'slug'        => 'historias',
      ],
      'supports'            => [ 'title', 'thumbnail', 'page-attributes' ],
      'taxonomies'          => [],
    ];

    $this->register_wp_post_type( $this->slug, $args );
  }
}
