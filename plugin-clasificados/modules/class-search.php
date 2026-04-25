<?php
/**
 * Clase para el Buscador Principal que enruta hacia los Silos SEO
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

class Clasificados_Search {

	public function __construct() {
		// Registrar shortcode del buscador
		add_shortcode( 'buscador_silo', array( $this, 'render_shortcode_buscador' ) );

		// Interceptar peticiones del formulario para redireccionar a URLs limpias
		add_action( 'template_redirect', array( $this, 'procesar_busqueda_silo' ) );

		// Cargar scripts necesarios para los selectores dependientes (Ciudad -> Distrito)
		add_action( 'wp_enqueue_scripts', array( $this, 'cargar_scripts_buscador' ) );

		// Endpoint AJAX para cargar distritos según la ciudad
		add_action( 'wp_ajax_nopriv_obtener_distritos', array( $this, 'ajax_obtener_distritos' ) );
		add_action( 'wp_ajax_obtener_distritos', array( $this, 'ajax_obtener_distritos' ) );
	}

	public function cargar_scripts_buscador() {
		// Encolaremos un script en línea directamente en el shortcode para mayor simplicidad
		// y para no depender de archivos JS externos si no es necesario.
	}

	public function render_shortcode_buscador( $atts ) {
		// Obtener categorías principales
		$categorias = get_terms( array(
			'taxonomy'   => 'categoria_anuncio',
			'hide_empty' => false,
			'parent'     => 0, // Solo categorías principales para el buscador root
		) );

		// Obtener ciudades (Ubicaciones principales, parent = 0)
		$ciudades = get_terms( array(
			'taxonomy'   => 'ubicacion',
			'hide_empty' => false,
			'parent'     => 0,
		) );

		ob_start();
		?>
		<div class="clasificados-buscador-silo" style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
			<form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="GET" class="form-buscador-silo" style="display: flex; flex-wrap: wrap; gap: 10px;">
				<input type="hidden" name="silo_search" value="1" />

				<div style="flex: 1; min-width: 200px;">
					<label for="silo_cat" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( '¿Qué buscas?', 'clasificados' ); ?></label>
					<select name="silo_cat" id="silo_cat" required style="width: 100%; padding: 10px;">
						<option value=""><?php esc_html_e( 'Todas las categorías', 'clasificados' ); ?></option>
						<?php foreach ( $categorias as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->slug ); ?>"><?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div style="flex: 1; min-width: 200px;">
					<label for="silo_ciudad" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Ciudad', 'clasificados' ); ?></label>
					<select name="silo_ciudad" id="silo_ciudad" style="width: 100%; padding: 10px;">
						<option value=""><?php esc_html_e( 'Cualquier Ciudad', 'clasificados' ); ?></option>
						<?php foreach ( $ciudades as $ciudad ) : ?>
							<option value="<?php echo esc_attr( $ciudad->term_id ); ?>" data-slug="<?php echo esc_attr( $ciudad->slug ); ?>"><?php echo esc_html( $ciudad->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<input type="hidden" name="silo_ciudad_slug" id="silo_ciudad_slug" value="" />
				</div>

				<div style="flex: 1; min-width: 200px;">
					<label for="silo_distrito" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Distrito', 'clasificados' ); ?></label>
					<select name="silo_distrito" id="silo_distrito" disabled style="width: 100%; padding: 10px;">
						<option value=""><?php esc_html_e( 'Cualquier Distrito', 'clasificados' ); ?></option>
					</select>
				</div>

				<div style="flex: 0 0 100%; margin-top: 10px;">
					<button type="submit" style="width: 100%; padding: 12px; background: #0073aa; color: #fff; border: none; font-size: 1.1em; font-weight: bold; cursor: pointer; border-radius: 4px;">
						<?php esc_html_e( 'Buscar Anuncios', 'clasificados' ); ?>
					</button>
				</div>
			</form>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			var ciudadSelect = document.getElementById('silo_ciudad');
			var ciudadSlugInput = document.getElementById('silo_ciudad_slug');
			var distritoSelect = document.getElementById('silo_distrito');

			ciudadSelect.addEventListener('change', function() {
				var cityId = this.value;
				var citySlug = this.options[this.selectedIndex].getAttribute('data-slug');
				ciudadSlugInput.value = citySlug || '';

				distritoSelect.innerHTML = '<option value="">Cargando distritos...</option>';
				distritoSelect.disabled = true;

				if (cityId) {
					var formData = new FormData();
					formData.append('action', 'obtener_distritos');
					formData.append('city_id', cityId);

					fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						distritoSelect.innerHTML = '<option value="">Cualquier Distrito</option>';
						if (data.success && data.data.length > 0) {
							data.data.forEach(function(distrito) {
								distritoSelect.innerHTML += '<option value="' + distrito.slug + '">' + distrito.name + '</option>';
							});
							distritoSelect.disabled = false;
						} else {
							distritoSelect.innerHTML = '<option value="">Sin distritos disponibles</option>';
						}
					})
					.catch(error => console.error('Error:', error));
				} else {
					distritoSelect.innerHTML = '<option value="">Cualquier Distrito</option>';
				}
			});
		});
		</script>
		<?php
		return ob_get_clean();
	}

	public function ajax_obtener_distritos() {
		$city_id = isset( $_POST['city_id'] ) ? intval( $_POST['city_id'] ) : 0;

		if ( ! $city_id ) {
			wp_send_json_error( 'ID de ciudad no válido' );
		}

		$distritos = get_terms( array(
			'taxonomy'   => 'ubicacion',
			'hide_empty' => false,
			'parent'     => $city_id,
		) );

		if ( is_wp_error( $distritos ) ) {
			wp_send_json_error( 'Error al obtener distritos' );
		}

		$data = array();
		foreach ( $distritos as $distrito ) {
			$data[] = array(
				'slug' => $distrito->slug,
				'name' => $distrito->name,
			);
		}

		wp_send_json_success( $data );
	}

	public function procesar_busqueda_silo() {
		// Solo procesar si viene de nuestro formulario de búsqueda SEO
		if ( isset( $_GET['silo_search'] ) && '1' === $_GET['silo_search'] ) {
			$cat_slug    = isset( $_GET['silo_cat'] ) ? sanitize_title( $_GET['silo_cat'] ) : '';
			$ciudad_slug = isset( $_GET['silo_ciudad_slug'] ) ? sanitize_title( $_GET['silo_ciudad_slug'] ) : '';
			$distrito_slug = isset( $_GET['silo_distrito'] ) ? sanitize_title( $_GET['silo_distrito'] ) : '';

			// Construir URL Base
			$url = home_url( '/' );

			// Siempre debe haber categoría (el form lo hace required, pero verificamos por seguridad)
			if ( $cat_slug ) {
				$url .= $cat_slug . '/';

				if ( $ciudad_slug ) {
					$url .= $ciudad_slug . '/';

					if ( $distrito_slug ) {
						$url .= $distrito_slug . '/';
					}
				}
			}

			// Redirección 301 para educar a Google y consolidar PageRank
			wp_redirect( esc_url_raw( $url ), 301 );
			exit;
		}
	}
}
