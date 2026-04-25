<?php
/**
 * Clase para manejar el SEO Programático (Títulos, Metas, H1, Breadcrumbs)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

class Clasificados_SEO {

	public function __construct() {
		// Títulos nativos de WP
		add_filter( 'document_title_parts', array( $this, 'modificar_titulo_documento' ) );

		// Compatibilidad con Yoast SEO
		add_filter( 'wpseo_title', array( $this, 'modificar_titulo_yoast' ) );
		add_filter( 'wpseo_metadesc', array( $this, 'modificar_metadesc_yoast' ) );

		// Compatibilidad con Rank Math
		add_filter( 'rank_math/frontend/title', array( $this, 'modificar_titulo_rankmath' ) );
		add_filter( 'rank_math/frontend/description', array( $this, 'modificar_metadesc_rankmath' ) );

		// Meta description nativa (fallback)
		add_action( 'wp_head', array( $this, 'añadir_meta_descripcion_nativa' ), 1 );
	}

	private function get_seo_data() {
		$cat      = get_query_var( 'clasificados_cat' );
		$ciudad   = get_query_var( 'clasificados_ciudad' );
		$distrito = get_query_var( 'clasificados_distrito' );

		if ( ! $cat && ! $ciudad && ! $distrito ) {
			return false;
		}

		$term_cat      = get_term_by( 'slug', $cat, 'categoria_anuncio' );
		$term_ciudad   = get_term_by( 'slug', $ciudad, 'ubicacion' );
		$term_distrito = get_term_by( 'slug', $distrito, 'ubicacion' );

		$cat_name      = $term_cat ? $term_cat->name : '';
		$ciudad_name   = $term_ciudad ? $term_ciudad->name : '';
		$distrito_name = $term_distrito ? $term_distrito->name : '';

		return array(
			'cat_slug'      => $cat,
			'ciudad_slug'   => $ciudad,
			'distrito_slug' => $distrito,
			'cat'           => $cat_name,
			'ciudad'        => $ciudad_name,
			'distrito'      => $distrito_name,
		);
	}

	public function generar_titulo() {
		$data = $this->get_seo_data();
		if ( ! $data ) return false;

		$partes = array();
		if ( $data['cat'] ) {
			$partes[] = $data['cat'];
		}

		$ubicacion = array();
		if ( $data['distrito'] ) {
			$ubicacion[] = $data['distrito'];
		}
		if ( $data['ciudad'] ) {
			$ubicacion[] = $data['ciudad'];
		}

		if ( ! empty( $ubicacion ) ) {
			$partes[] = 'en ' . implode( ', ', $ubicacion );
		}

		$partes[] = '| Compra y Venta';

		return implode( ' ', $partes );
	}

	public function generar_meta_descripcion() {
		$data = $this->get_seo_data();
		if ( ! $data ) return false;

		$cat_text = $data['cat'] ? strtolower( $data['cat'] ) : 'anuncios';
		$ubi_text = '';

		if ( $data['distrito'] && $data['ciudad'] ) {
			$ubi_text = ' en ' . $data['distrito'] . ', ' . $data['ciudad'];
		} elseif ( $data['ciudad'] ) {
			$ubi_text = ' en ' . $data['ciudad'];
		}

		return "Encuentra los mejores {$cat_text}{$ubi_text}. Compra, vende y descubre oportunidades increíbles en nuestra plataforma de clasificados.";
	}

	public function modificar_titulo_documento( $title ) {
		$nuevo_titulo = $this->generar_titulo();
		if ( $nuevo_titulo ) {
			$title['title'] = $nuevo_titulo;
			unset( $title['site'] ); // Opcional: remover el nombre del sitio para que quede limpio
		}
		return $title;
	}

	public function añadir_meta_descripcion_nativa() {
		// Solo añadir si Yoast/RankMath no están activos
		if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) {
			return;
		}

		$meta_desc = $this->generar_meta_descripcion();
		if ( $meta_desc ) {
			echo '<meta name="description" content="' . esc_attr( $meta_desc ) . '" />' . "\n";
		}
	}

	// --- Integraciones Plugins SEO ---

	public function modificar_titulo_yoast( $title ) {
		$nuevo_titulo = $this->generar_titulo();
		return $nuevo_titulo ? $nuevo_titulo : $title;
	}

	public function modificar_metadesc_yoast( $desc ) {
		$nueva_desc = $this->generar_meta_descripcion();
		return $nueva_desc ? $nueva_desc : $desc;
	}

	public function modificar_titulo_rankmath( $title ) {
		$nuevo_titulo = $this->generar_titulo();
		return $nuevo_titulo ? $nuevo_titulo : $title;
	}

	public function modificar_metadesc_rankmath( $desc ) {
		$nueva_desc = $this->generar_meta_descripcion();
		return $nueva_desc ? $nueva_desc : $desc;
	}

	// Helper estático para el H1 dinámico
	public static function get_h1() {
		$instancia = new self();
		$data = $instancia->get_seo_data();

		if ( ! $data ) return post_type_archive_title( '', false );

		$cat_text = $data['cat'] ? $data['cat'] : 'Anuncios';

		$ubi_text = '';
		if ( $data['distrito'] && $data['ciudad'] ) {
			$ubi_text = ' en ' . $data['distrito'] . ', ' . $data['ciudad'];
		} elseif ( $data['ciudad'] ) {
			$ubi_text = ' en ' . $data['ciudad'];
		}

		return esc_html( $cat_text . $ubi_text );
	}

	// Generador de Breadcrumbs
	public static function get_breadcrumbs() {
		$instancia = new self();
		$data = $instancia->get_seo_data();

		$home_url = home_url( '/' );
		$breadcrumbs = '<nav aria-label="breadcrumb" class="clasificados-breadcrumbs" style="font-size: 0.9em; margin-bottom: 20px;">';
		$breadcrumbs .= '<ol style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 5px;">';
		$breadcrumbs .= '<li><a href="' . esc_url( $home_url ) . '">' . __( 'Inicio', 'clasificados' ) . '</a></li>';

		if ( ! $data ) {
			if ( is_post_type_archive( 'anuncio' ) ) {
				$breadcrumbs .= '<li>&raquo;</li><li>' . __( 'Anuncios', 'clasificados' ) . '</li>';
			} elseif ( is_singular( 'anuncio' ) ) {
				$breadcrumbs .= '<li>&raquo;</li><li>' . get_the_title() . '</li>';
			}
			$breadcrumbs .= '</ol></nav>';
			return $breadcrumbs;
		}

		// Construir URL Base del Silo (Categoría)
		if ( $data['cat'] ) {
			$cat_url = $home_url . $data['cat_slug'] . '/';
			$breadcrumbs .= '<li>&raquo;</li><li><a href="' . esc_url( $cat_url ) . '">' . esc_html( $data['cat'] ) . '</a></li>';

			if ( $data['ciudad'] ) {
				$ciudad_url = $cat_url . $data['ciudad_slug'] . '/';
				$breadcrumbs .= '<li>&raquo;</li><li><a href="' . esc_url( $ciudad_url ) . '">' . esc_html( $data['ciudad'] ) . '</a></li>';

				if ( $data['distrito'] ) {
					$distrito_url = $ciudad_url . $data['distrito_slug'] . '/';
					$breadcrumbs .= '<li>&raquo;</li><li><a href="' . esc_url( $distrito_url ) . '">' . esc_html( $data['distrito'] ) . '</a></li>';
				}
			}
		}

		$breadcrumbs .= '</ol></nav>';
		return $breadcrumbs;
	}
}
