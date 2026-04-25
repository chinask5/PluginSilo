<?php
/**
 * Clase para manejar el SEO Silo a nivel de URLs y Rewrite Rules
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

class Clasificados_Rewrite {

	public function __construct() {
		// Registrar query vars personalizados
		add_filter( 'query_vars', array( $this, 'registrar_query_vars' ) );

		// Añadir las rewrite rules
		add_action( 'init', array( $this, 'añadir_rewrite_rules' ), 10 );

		// Interceptar la consulta principal para forzar variables
		add_action( 'pre_get_posts', array( $this, 'modificar_consulta_principal' ) );

		// Limpiar transient cuando se crean, editan o eliminan categorías
		add_action( 'create_categoria_anuncio', array( $this, 'limpiar_transients' ) );
		add_action( 'edit_categoria_anuncio', array( $this, 'limpiar_transients' ) );
		add_action( 'delete_categoria_anuncio', array( $this, 'limpiar_transients' ) );
	}

	public function registrar_query_vars( $vars ) {
		$vars[] = 'clasificados_cat';
		$vars[] = 'clasificados_ciudad';
		$vars[] = 'clasificados_distrito';
		return $vars;
	}

	public function limpiar_transients() {
		delete_transient( 'clasificados_cats_regex' );
		// No hacemos flush_rewrite_rules aquí para evitar problemas de rendimiento
		// durante importaciones masivas. El flush debe hacerse manualmente
		// desde el panel de administración.
	}

	private function obtener_regex_categorias() {
		$regex = get_transient( 'clasificados_cats_regex' );

		if ( false === $regex ) {
			$terms = get_terms( array(
				'taxonomy'   => 'categoria_anuncio',
				'hide_empty' => false,
			) );

			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$paths = array();
				foreach ( $terms as $term ) {
					$slugs = array();
					$current = $term;
					while ( $current ) {
						array_unshift( $slugs, $current->slug );
						if ( $current->parent ) {
							$current = get_term( $current->parent, 'categoria_anuncio' );
						} else {
							break;
						}
					}
					$paths[] = implode( '/', $slugs );
				}

				// Ordenar por longitud descendente para que las subcategorías (rutas largas)
				// se evalúen antes que las categorías padre (rutas cortas) en el regex.
				usort( $paths, function( $a, $b ) {
					return strlen( $b ) - strlen( $a );
				} );

				// Escapar las barras para usarlas en Regex de WP
				$escaped_paths = array_map( function( $path ) {
					// No necesitamos usar preg_quote porque WP Rewrite Rules maneja strings literales bien,
					// pero como esto irá dentro de (a|b), los paths con / funcionan de forma nativa.
					return $path;
				}, $paths );

				$regex = implode( '|', $escaped_paths );

				// Cache por 1 día (o hasta que se limpie manualmente en hooks)
				set_transient( 'clasificados_cats_regex', $regex, DAY_IN_SECONDS );
			}
		}

		return $regex;
	}

	public function añadir_rewrite_rules() {
		$slugs_regex = $this->obtener_regex_categorias();

		if ( $slugs_regex ) {
			// Reglas con paginación incluidas

			// 1. /{categoria}/{ciudad}/{distrito}/page/2/
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/([^/]+)/([^/]+)/page/([0-9]{1,})/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]&clasificados_ciudad=$matches[2]&clasificados_distrito=$matches[3]&paged=$matches[4]',
				'top'
			);

			// 2. /{categoria}/{ciudad}/{distrito}/
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/([^/]+)/([^/]+)/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]&clasificados_ciudad=$matches[2]&clasificados_distrito=$matches[3]',
				'top'
			);

			// 3. /{categoria}/{ciudad}/page/2/
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/([^/]+)/page/([0-9]{1,})/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]&clasificados_ciudad=$matches[2]&paged=$matches[3]',
				'top'
			);

			// 4. /{categoria}/{ciudad}/
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/([^/]+)/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]&clasificados_ciudad=$matches[2]',
				'top'
			);

			// 5. /{categoria}/page/2/
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/page/([0-9]{1,})/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]&paged=$matches[2]',
				'top'
			);

			// 6. /{categoria}/
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]',
				'top'
			);
		}
	}

	public function modificar_consulta_principal( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$cat      = get_query_var( 'clasificados_cat' );
		$ciudad   = get_query_var( 'clasificados_ciudad' );
		$distrito = get_query_var( 'clasificados_distrito' );

		if ( $cat || $ciudad || $distrito ) {
			$tax_query = array( 'relation' => 'AND' );

			if ( $cat ) {
				// $cat puede venir como 'vehiculos/autopartes'
				$cat_parts = explode( '/', $cat );
				$final_cat_slug = end( $cat_parts );

				$tax_query[] = array(
					'taxonomy' => 'categoria_anuncio',
					'field'    => 'slug',
					'terms'    => $final_cat_slug,
				);
			}

			// Si hay distrito, filtramos primariamente por el distrito.
			// En la interfaz de WP es común que solo se marque el término hijo (San Borja)
			// y no el término padre (Lima). Si forzamos un AND, el anuncio no aparecerá
			// a menos que el usuario marque ambos checkboxes manualmente.
			if ( $distrito ) {
				$tax_query[] = array(
					'taxonomy' => 'ubicacion',
					'field'    => 'slug',
					'terms'    => $distrito,
				);
			} elseif ( $ciudad ) {
				$tax_query[] = array(
					'taxonomy' => 'ubicacion',
					'field'    => 'slug',
					'terms'    => $ciudad,
				);
			}

			if ( count( $tax_query ) > 1 ) {
				$query->set( 'tax_query', $tax_query );
			}
		}
	}
}
