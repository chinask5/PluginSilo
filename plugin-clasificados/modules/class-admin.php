<?php
/**
 * Clase para manejar el Panel de Administración del Plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

class Clasificados_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'añadir_menu_admin' ) );
		add_action( 'admin_init', array( $this, 'registrar_ajustes' ) );
	}

	public function añadir_menu_admin() {
		add_menu_page(
			__( 'Configuración Clasificados', 'clasificados' ), // Título de la página
			__( 'Clasificados SEO', 'clasificados' ),           // Título del menú
			'manage_options',                                   // Capacidad requerida
			'clasificados-seo-ajustes',                         // Slug del menú
			array( $this, 'render_pagina_ajustes' ),            // Callback para renderizar
			'dashicons-admin-generic',                          // Icono
			80                                                  // Posición
		);
	}

	public function registrar_ajustes() {
		register_setting( 'clasificados_opciones_grupo', 'clasificados_ajustes' );

		add_settings_section(
			'clasificados_seccion_principal',
			__( 'Ajustes Principales', 'clasificados' ),
			array( $this, 'render_seccion_principal' ),
			'clasificados-seo-ajustes'
		);

		add_settings_field(
			'modulo_seo_activo',
			__( 'Activar Módulo SEO Programático', 'clasificados' ),
			array( $this, 'render_campo_modulo_seo' ),
			'clasificados-seo-ajustes',
			'clasificados_seccion_principal'
		);

		add_settings_field(
			'limpiar_cache',
			__( 'Limpiar Caché de URLs', 'clasificados' ),
			array( $this, 'render_campo_limpiar_cache' ),
			'clasificados-seo-ajustes',
			'clasificados_seccion_principal'
		);
	}

	public function render_seccion_principal() {
		echo '<p>' . esc_html__( 'Configura los módulos y opciones de caché para el sistema de SEO Silos.', 'clasificados' ) . '</p>';
	}

	public function render_campo_modulo_seo() {
		$opciones = get_option( 'clasificados_ajustes' );
		// Si la opción no existe (false), por defecto es '1'.
		// Si la opción existe pero 'modulo_seo_activo' no está seteado, significa que se guardó desmarcado ('0').
		if ( false === $opciones ) {
			$activo = '1';
		} else {
			$activo = isset( $opciones['modulo_seo_activo'] ) ? $opciones['modulo_seo_activo'] : '0';
		}
		?>
		<label>
			<!-- Hidden input para asegurar que se envíe '0' si el checkbox está desmarcado -->
			<input type="hidden" name="clasificados_ajustes[modulo_seo_activo]" value="0" />
			<input type="checkbox" name="clasificados_ajustes[modulo_seo_activo]" value="1" <?php checked( '1', $activo ); ?> />
			<?php esc_html_e( 'Activar autogeneración de Títulos, Metas y H1.', 'clasificados' ); ?>
		</label>
		<?php
	}

	public function render_campo_limpiar_cache() {
		?>
		<p class="description">
			<?php esc_html_e( 'Si has añadido nuevas categorías y las URLs no funcionan (Error 404), guarda los cambios aquí para regenerar las reglas.', 'clasificados' ); ?>
		</p>
		<?php
		// Lógica simple para limpiar transient si se envía la página
		if ( isset( $_GET['settings-updated'] ) ) {
			delete_transient( 'clasificados_cats_regex' );
			flush_rewrite_rules();
		}
	}

	public function render_pagina_ajustes() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'clasificados_opciones_grupo' );
				do_settings_sections( 'clasificados-seo-ajustes' );
				submit_button( __( 'Guardar Ajustes y Regenerar Permalinks', 'clasificados' ) );
				?>
			</form>
		</div>
		<?php
	}
}
