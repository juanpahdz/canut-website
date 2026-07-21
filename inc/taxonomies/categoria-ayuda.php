<?php
/**
 * The Categoria de ayuda taxonomy class.
 *
 * Groups Recurso_Ayuda posts into the sections shown on the Centro de ayuda
 * hub (Sobre el producto, Envíos, Pagos, Garantía, Mi mascota, ...). Icon
 * and WhatsApp CTA copy per category live in inc/acf-fields/categoria-ayuda.php.
 *
 * @package air-light
 */

namespace Air_Light;

/**
 * Registers the Categoria de ayuda taxonomy.
 *
 * @param Array $post_types Optional. Post types in
 * which the taxonomy should be registered.
 */
class Categoria_Ayuda extends Taxonomy {

  public function register( array $post_types = [] ) {
    $labels = [
      'name'                  => self::ask__( 'Categoria ayuda', 'Categorías de ayuda' ),
      'singular_name'         => self::ask__( 'Categoria ayuda', 'Categoría de ayuda' ),
      'search_items'          => self::ask__( 'Categoria ayuda', 'Buscar categorías de ayuda' ),
      'popular_items'         => self::ask__( 'Categoria ayuda', 'Categorías de ayuda populares' ),
      'all_items'             => self::ask__( 'Categoria ayuda', 'Todas las categorías de ayuda' ),
      'parent_item'           => self::ask__( 'Categoria ayuda', 'Categoría superior' ),
      'parent_item_colon'     => self::ask__( 'Categoria ayuda', 'Categoría superior:' ),
      'edit_item'             => self::ask__( 'Categoria ayuda', 'Editar categoría de ayuda' ),
      'update_item'           => self::ask__( 'Categoria ayuda', 'Actualizar categoría de ayuda' ),
      'add_new_item'          => self::ask__( 'Categoria ayuda', 'Añadir nueva categoría de ayuda' ),
      'new_item_name'         => self::ask__( 'Categoria ayuda', 'Nueva categoría de ayuda' ),
      'add_or_remove_items'   => self::ask__( 'Categoria ayuda', 'Añadir o quitar categorías de ayuda' ),
      'choose_from_most_used' => self::ask__( 'Categoria ayuda', 'Elegir entre las categorías más usadas' ),
      'menu_name'             => self::ask__( 'Categoria ayuda', 'Categorías de ayuda' ),
    ];

    $args = [
      'labels'            => $labels,
      'public'            => true,
      'show_ui'           => true,
      'show_in_rest'      => true,
      'show_in_nav_menus' => false,
      'show_admin_column' => true,
      'hierarchical'      => true,
      'show_tagcloud'     => false,
      'query_var'         => true,
      'pll_translatable'  => true,
      'rewrite'           => [
        'slug' => 'categoria-ayuda',
      ],
    ];

    $this->register_wp_taxonomy( $this->slug, $post_types, $args );
  }
}
