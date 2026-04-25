<?php
/**
 * Clase para el registro de taxonomías
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

class Clasificados_Taxonomies {

	public function __construct() {
		add_action( 'init', array( $this, 'registrar_taxonomias' ), 0 );
	}

	public function registrar_taxonomias() {
		// Taxonomía: Categoría de Anuncio
		$labels_cat = array(
			'name'                       => _x( 'Categorías', 'Taxonomy General Name', 'clasificados' ),
			'singular_name'              => _x( 'Categoría', 'Taxonomy Singular Name', 'clasificados' ),
			'menu_name'                  => __( 'Categorías', 'clasificados' ),
			'all_items'                  => __( 'Todas las Categorías', 'clasificados' ),
			'parent_item'                => __( 'Categoría Padre', 'clasificados' ),
			'parent_item_colon'          => __( 'Categoría Padre:', 'clasificados' ),
			'new_item_name'              => __( 'Nombre de Nueva Categoría', 'clasificados' ),
			'add_new_item'               => __( 'Añadir Nueva Categoría', 'clasificados' ),
			'edit_item'                  => __( 'Editar Categoría', 'clasificados' ),
			'update_item'                => __( 'Actualizar Categoría', 'clasificados' ),
			'view_item'                  => __( 'Ver Categoría', 'clasificados' ),
			'separate_items_with_commas' => __( 'Separar categorías con comas', 'clasificados' ),
			'add_or_remove_items'        => __( 'Añadir o remover categorías', 'clasificados' ),
			'choose_from_most_used'      => __( 'Elegir de las más usadas', 'clasificados' ),
			'popular_items'              => __( 'Categorías Populares', 'clasificados' ),
			'search_items'               => __( 'Buscar Categorías', 'clasificados' ),
			'not_found'                  => __( 'No encontrado', 'clasificados' ),
			'no_terms'                   => __( 'No hay categorías', 'clasificados' ),
			'items_list'                 => __( 'Lista de categorías', 'clasificados' ),
			'items_list_navigation'      => __( 'Navegación de lista de categorías', 'clasificados' ),
		);
		$args_cat = array(
			'labels'                     => $labels_cat,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'show_in_rest'               => true,
			'rewrite'                    => array( 'slug' => 'categoria-anuncio' ),
		);
		register_taxonomy( 'categoria_anuncio', array( 'anuncio' ), $args_cat );

		// Taxonomía: Ubicación (País > Ciudad > Distrito)
		$labels_ubi = array(
			'name'                       => _x( 'Ubicaciones', 'Taxonomy General Name', 'clasificados' ),
			'singular_name'              => _x( 'Ubicación', 'Taxonomy Singular Name', 'clasificados' ),
			'menu_name'                  => __( 'Ubicaciones', 'clasificados' ),
			'all_items'                  => __( 'Todas las Ubicaciones', 'clasificados' ),
			'parent_item'                => __( 'Ubicación Padre', 'clasificados' ),
			'parent_item_colon'          => __( 'Ubicación Padre:', 'clasificados' ),
			'new_item_name'              => __( 'Nombre de Nueva Ubicación', 'clasificados' ),
			'add_new_item'               => __( 'Añadir Nueva Ubicación', 'clasificados' ),
			'edit_item'                  => __( 'Editar Ubicación', 'clasificados' ),
			'update_item'                => __( 'Actualizar Ubicación', 'clasificados' ),
			'view_item'                  => __( 'Ver Ubicación', 'clasificados' ),
			'separate_items_with_commas' => __( 'Separar ubicaciones con comas', 'clasificados' ),
			'add_or_remove_items'        => __( 'Añadir o remover ubicaciones', 'clasificados' ),
			'choose_from_most_used'      => __( 'Elegir de las más usadas', 'clasificados' ),
			'popular_items'              => __( 'Ubicaciones Populares', 'clasificados' ),
			'search_items'               => __( 'Buscar Ubicaciones', 'clasificados' ),
			'not_found'                  => __( 'No encontrado', 'clasificados' ),
			'no_terms'                   => __( 'No hay ubicaciones', 'clasificados' ),
			'items_list'                 => __( 'Lista de ubicaciones', 'clasificados' ),
			'items_list_navigation'      => __( 'Navegación de lista de ubicaciones', 'clasificados' ),
		);
		$args_ubi = array(
			'labels'                     => $labels_ubi,
			'hierarchical'               => true, // Estructura Padre > Hijo
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'show_in_rest'               => true,
			'rewrite'                    => array( 'slug' => 'ubicacion' ),
		);
		register_taxonomy( 'ubicacion', array( 'anuncio' ), $args_ubi );
	}
}
