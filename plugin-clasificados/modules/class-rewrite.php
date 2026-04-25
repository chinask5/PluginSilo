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

		// Interceptar la generación de enlaces de términos para quitar la base
		add_filter( 'term_link', array( $this, 'modificar_enlaces_terminos' ), 10, 3 );

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

		// Forzamos la regeneración del regex y lo guardamos ANTES de hacer el flush.
		// Esto asegura que flush_rewrite_rules() lea el regex con las nuevas subcategorías.
		$this->obtener_regex_categorias();

		// Reactivamos el flush automático a petición del usuario para facilitar
		// el flujo de trabajo manual sin tener que ir a los ajustes cada vez.
		flush_rewrite_rules();
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
			// Las reglas deben priorizar aquellas que eviten solapamientos.
			// Si la URL es mascotas/gatos, podría coincidir con /{categoria}/{ciudad} (cat:mascotas, ciudad:gatos)
			// PERO si mascotas/gatos es una categoría válida, debe atraparla.
			// Al estar las categorías listadas de mayor a menor longitud en $slugs_regex,
			// WP regex Engine evaluará "mascotas/gatos" completo antes que "mascotas".

			// Sin embargo, hay un detalle con la precedencia de add_rewrite_rule usando 'top':
			// WordPress hace "prepend" de estas reglas en su array interno.
			// Esto significa que LA ÚLTIMA regla que definamos aquí será LA PRIMERA en evaluarse.
			// Queremos que las rutas más precisas e íntegras (subcategoría completa) se evalúen antes
			// que las reglas que intentan dividir el final de la URL asumiendo que es una ciudad.

			// 1. /{categoria}/{ciudad}/{distrito}/page/2/ (Más específica)
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/([^/]+)/([^/]+)/page/([0-9]{1,})/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]&clasificados_ciudad=$matches[2]&clasificados_distrito=$matches[3]&paged=$matches[4]',
				'top'
			);

			// 2. /{categoria}/{ciudad}/{distrito}/ (Más específica)
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
			// Usamos (?!page) para asegurar que no atrape URLs de paginación
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/(?!page)([^/]+)/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]&clasificados_ciudad=$matches[2]',
				'top'
			);

			// 5. /{categoria}/page/2/
			// Como se define DESPUÉS de {categoria}/{ciudad}/, en el array final estará ARRIBA,
			// priorizando que si "mascotas/gatos/page/2" es una categoría completa, se atrape aquí primero.
			add_rewrite_rule(
				'^(' . $slugs_regex . ')/page/([0-9]{1,})/?$',
				'index.php?post_type=anuncio&clasificados_cat=$matches[1]&paged=$matches[2]',
				'top'
			);

			// 6. /{categoria}/
			// Como se define de último, será la PRIMERA regla evaluada por WordPress.
			// Así "mascotas/gatos" entra perfecto aquí y no cae en la regla #4 por error.
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
			$is_invalid = false; // Bandera para forzar 404 si un slug no existe

			if ( $cat ) {
				// $cat puede venir como 'vehiculos/autopartes'
				$cat_parts = explode( '/', $cat );
				$final_cat_slug = end( $cat_parts );

				// Buscar el ID exacto del término para evitar bugs de resolución jerárquica con slugs
				$term_cat = get_term_by( 'slug', $final_cat_slug, 'categoria_anuncio' );

				if ( $term_cat ) {
					$tax_query[] = array(
						'taxonomy' => 'categoria_anuncio',
						'field'    => 'term_id',
						'terms'    => $term_cat->term_id,
					);
				} else {
					$is_invalid = true;
				}
			}

			// Si hay distrito, filtramos primariamente por el distrito.
			if ( $distrito ) {
				$term_dist = get_term_by( 'slug', $distrito, 'ubicacion' );
				if ( $term_dist ) {
					$tax_query[] = array(
						'taxonomy' => 'ubicacion',
						'field'    => 'term_id',
						'terms'    => $term_dist->term_id,
					);
				} else {
					$is_invalid = true;
				}
			} elseif ( $ciudad ) {
				$term_ciu = get_term_by( 'slug', $ciudad, 'ubicacion' );
				if ( $term_ciu ) {
					$tax_query[] = array(
						'taxonomy' => 'ubicacion',
						'field'    => 'term_id',
						'terms'    => $term_ciu->term_id,
					);
				} else {
					$is_invalid = true;
				}
			}

			if ( $is_invalid ) {
				// Si pasaron una ciudad/distrito/categoría que no existe en la BD, forzar 404.
				// Esto evita falsos positivos donde WP ignora el filtro fallido y muestra toda la lista.
				$query->set_404();
				return;
			}

			if ( count( $tax_query ) > 1 ) {
				$query->set( 'tax_query', $tax_query );
			}
		}
	}

	public function modificar_enlaces_terminos( $url, $term, $taxonomy ) {
		if ( 'categoria_anuncio' === $taxonomy ) {
			// Construir la ruta jerárquica del término
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
			$path = implode( '/', $slugs );

			// Reemplazar la URL base de WordPress por nuestra ruta limpia
			$url = home_url( user_trailingslashit( $path, 'category' ) );
		}
		return $url;
	}
}
