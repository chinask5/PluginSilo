<?php
/**
 * Clase para cargar las plantillas del plugin o permitir overrides del tema
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

class Clasificados_Template_Loader {

	public function __construct() {
		add_filter( 'template_include', array( $this, 'cargar_plantilla' ) );
	}

	public function cargar_plantilla( $template ) {
		// Verificar si estamos en un archive de anuncios o si hay query vars de silo
		$is_anuncio_query = get_query_var( 'clasificados_cat' ) || get_query_var( 'clasificados_ciudad' ) || get_query_var( 'clasificados_distrito' );

		if ( is_post_type_archive( 'anuncio' ) || $is_anuncio_query ) {
			$nuevo_template = $this->obtener_ruta_plantilla( 'archive-anuncios.php' );
			if ( $nuevo_template ) {
				return $nuevo_template;
			}
		}

		if ( is_singular( 'anuncio' ) ) {
			$nuevo_template = $this->obtener_ruta_plantilla( 'single-anuncio.php' );
			if ( $nuevo_template ) {
				return $nuevo_template;
			}
		}

		return $template;
	}

	private function obtener_ruta_plantilla( $nombre_archivo ) {
		// 1. Buscar en el tema hijo o tema padre (por ejemplo dentro de una carpeta 'clasificados')
		$ruta_tema = locate_template( array(
			'clasificados/' . $nombre_archivo,
			$nombre_archivo,
		) );

		if ( $ruta_tema ) {
			return $ruta_tema;
		}

		// 2. Si no existe en el tema, cargar desde el plugin
		$ruta_plugin = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $nombre_archivo;
		if ( file_exists( $ruta_plugin ) ) {
			return $ruta_plugin;
		}

		return false;
	}
}
