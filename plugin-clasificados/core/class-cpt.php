<?php
/**
 * Clase para el registro del Custom Post Type
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

class Clasificados_CPT {

	public function __construct() {
		add_action( 'init', array( $this, 'registrar_cpt_anuncio' ) );
	}

	public function registrar_cpt_anuncio() {
		$labels = array(
			'name'                  => _x( 'Anuncios', 'Post Type General Name', 'clasificados' ),
			'singular_name'         => _x( 'Anuncio', 'Post Type Singular Name', 'clasificados' ),
			'menu_name'             => __( 'Anuncios', 'clasificados' ),
			'name_admin_bar'        => __( 'Anuncio', 'clasificados' ),
			'archives'              => __( 'Archivos de Anuncios', 'clasificados' ),
			'attributes'            => __( 'Atributos de Anuncio', 'clasificados' ),
			'parent_item_colon'     => __( 'Anuncio Padre:', 'clasificados' ),
			'all_items'             => __( 'Todos los Anuncios', 'clasificados' ),
			'add_new_item'          => __( 'Añadir Nuevo Anuncio', 'clasificados' ),
			'add_new'               => __( 'Añadir Nuevo', 'clasificados' ),
			'new_item'              => __( 'Nuevo Anuncio', 'clasificados' ),
			'edit_item'             => __( 'Editar Anuncio', 'clasificados' ),
			'update_item'           => __( 'Actualizar Anuncio', 'clasificados' ),
			'view_item'             => __( 'Ver Anuncio', 'clasificados' ),
			'view_items'            => __( 'Ver Anuncios', 'clasificados' ),
			'search_items'          => __( 'Buscar Anuncio', 'clasificados' ),
			'not_found'             => __( 'No encontrado', 'clasificados' ),
			'not_found_in_trash'    => __( 'No encontrado en la Papelera', 'clasificados' ),
			'featured_image'        => __( 'Imagen Destacada', 'clasificados' ),
			'set_featured_image'    => __( 'Establecer imagen destacada', 'clasificados' ),
			'remove_featured_image' => __( 'Remover imagen destacada', 'clasificados' ),
			'use_featured_image'    => __( 'Usar como imagen destacada', 'clasificados' ),
			'insert_into_item'      => __( 'Insertar en el anuncio', 'clasificados' ),
			'uploaded_to_this_item' => __( 'Subido a este anuncio', 'clasificados' ),
			'items_list'            => __( 'Lista de anuncios', 'clasificados' ),
			'items_list_navigation' => __( 'Navegación de la lista de anuncios', 'clasificados' ),
			'filter_items_list'     => __( 'Filtrar lista de anuncios', 'clasificados' ),
		);
		$args = array(
			'label'                 => __( 'Anuncio', 'clasificados' ),
			'description'           => __( 'Anuncios clasificados', 'clasificados' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			'taxonomies'            => array( 'categoria_anuncio', 'ubicacion' ),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-megaphone',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => 'anuncios',
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true, // Para Gutenberg y API REST
			// Permite que las urls sean modificadas
			'rewrite'               => array( 'slug' => 'anuncio', 'with_front' => false ),
		);
		register_post_type( 'anuncio', $args );
	}
}
