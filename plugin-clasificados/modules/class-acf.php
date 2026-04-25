<?php
/**
 * Clase para la integración con Advanced Custom Fields (ACF Pro)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Salir si se accede directamente.
}

class Clasificados_ACF {

	public function __construct() {
		// Registrar campos ACF mediante PHP si ACF está activo
		add_action( 'acf/init', array( $this, 'registrar_campos_acf' ) );
	}

	public function registrar_campos_acf() {
		if ( function_exists( 'acf_add_local_field_group' ) ) {

			acf_add_local_field_group( array(
				'key' => 'group_clasificados_detalles',
				'title' => 'Detalles del Anuncio',
				'fields' => array(
					array(
						'key' => 'field_precio_anuncio',
						'label' => 'Precio',
						'name' => 'precio',
						'type' => 'number',
						'instructions' => 'Ingresa el precio del artículo/servicio.',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '50',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '$',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
					),
					array(
						'key' => 'field_telefono_contacto',
						'label' => 'Teléfono de Contacto',
						'name' => 'telefono_contacto',
						'type' => 'text',
						'instructions' => 'Teléfono o WhatsApp del vendedor.',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '50',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '+51 999 999 999',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'anuncio',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'normal',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
				'active' => true,
				'description' => 'Campos adicionales para los anuncios clasificados.',
			) );

		}
	}
}
