<?php
/**
 * Plugin Name: Clasificados SEO Programático
 * Description: Plugin modular y escalable para clasificados con estructura de silos SEO (/{categoria}/{ciudad}/{distrito}/).
 * Version: 1.0.0
 * Author: Tu Nombre
 * Text Domain: clasificados
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

// Constantes
define( 'CLASIFICADOS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CLASIFICADOS_URL', plugin_dir_url( __FILE__ ) );

// Includes Core
require_once CLASIFICADOS_DIR . 'core/class-cpt.php';
require_once CLASIFICADOS_DIR . 'core/class-taxonomies.php';

// Includes Modules
require_once CLASIFICADOS_DIR . 'modules/class-rewrite.php';
require_once CLASIFICADOS_DIR . 'modules/class-seo.php';
require_once CLASIFICADOS_DIR . 'modules/class-admin.php';
require_once CLASIFICADOS_DIR . 'modules/class-acf.php';
require_once CLASIFICADOS_DIR . 'modules/class-search.php';

// Includes Template Loader
require_once CLASIFICADOS_DIR . 'includes/class-template-loader.php';

/**
 * Clase principal que orquesta el plugin
 */
class Clasificados_Plugin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init();
	}

	private function init() {
		// Inicializar Core
		new Clasificados_CPT();
		new Clasificados_Taxonomies();

		// Inicializar Módulos
		new Clasificados_Rewrite();

		$opciones = get_option( 'clasificados_ajustes' );
		$seo_activo = isset( $opciones['modulo_seo_activo'] ) ? $opciones['modulo_seo_activo'] : '1';
		if ( '1' === $seo_activo ) {
			new Clasificados_SEO();
		}

		if ( is_admin() ) {
			new Clasificados_Admin();
		}

		new Clasificados_ACF();
		new Clasificados_Search();

		// Inicializar Cargador de Plantillas
		new Clasificados_Template_Loader();

		// Registrar hook de activación
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	/**
	 * Hook de activación. Instala datos del MVP y limpia reglas.
	 */
	public function activate() {
		// 1. Asegurarnos que CPT y Taxonomías estén registrados antes de insertar
		$cpt = new Clasificados_CPT();
		$cpt->registrar_cpt_anuncio();

		$tax = new Clasificados_Taxonomies();
		$tax->registrar_taxonomias();

		// 2. Insertar Categoría: Vehículos
		$cat_vehiculos = term_exists( 'vehiculos', 'categoria_anuncio' );
		if ( ! $cat_vehiculos ) {
			$cat_vehiculos = wp_insert_term(
				'Vehículos',
				'categoria_anuncio',
				array( 'slug' => 'vehiculos' )
			);
		}

		// 3. Insertar Ubicación: Lima (Ciudad)
		$ubi_lima = term_exists( 'lima', 'ubicacion' );
		if ( ! $ubi_lima ) {
			$ubi_lima = wp_insert_term(
				'Lima',
				'ubicacion',
				array( 'slug' => 'lima' )
			);
		}

		// 4. Insertar Ubicación: San Borja (Distrito, hijo de Lima)
		if ( ! is_wp_error( $ubi_lima ) && isset( $ubi_lima['term_id'] ) ) {
			$ubi_san_borja = term_exists( 'san-borja', 'ubicacion' );
			if ( ! $ubi_san_borja ) {
				wp_insert_term(
					'San Borja',
					'ubicacion',
					array(
						'slug'   => 'san-borja',
						'parent' => $ubi_lima['term_id']
					)
				);
			}
		}

		// 5. Añadir las reglas de reescritura dinámicas
		$rewrite = new Clasificados_Rewrite();
		$rewrite->añadir_rewrite_rules();

		// 6. Refrescar reglas de reescritura (flush)
		flush_rewrite_rules();
	}
}

// Iniciar el plugin
function run_clasificados_plugin() {
	Clasificados_Plugin::get_instance();
}
run_clasificados_plugin();
