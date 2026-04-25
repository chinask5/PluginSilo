<?php
/**
 * Plantilla para un anuncio individual (Single)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-anuncio-view' ); ?>>
				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

					<div class="anuncio-meta" style="margin-bottom: 20px; font-size: 0.9em; color: #666;">
						<?php
						$categorias = get_the_term_list( get_the_ID(), 'categoria_anuncio', 'Categoría: ', ', ' );
						$ubicaciones = get_the_term_list( get_the_ID(), 'ubicacion', ' | Ubicación: ', ', ' );

						if ( $categorias ) echo $categorias;
						if ( $ubicaciones ) echo $ubicaciones;
						?>
					</div>
				</header><!-- .entry-header -->

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="anuncio-thumbnail" style="margin-bottom: 20px;">
						<?php the_post_thumbnail( 'large', array( 'style' => 'max-width: 100%; height: auto;' ) ); ?>
					</div>
				<?php endif; ?>

				<div class="entry-content">
					<?php
					// Obtener precio y teléfono (Campos Generales)
					$precio = get_field( 'precio' );
					$telefono = get_field( 'telefono_contacto' );

					if ( $precio || $telefono ) :
					?>
						<div class="anuncio-destacados" style="background: #e9f5e9; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 1.1em;">
							<?php if ( $precio ) : ?>
								<p style="margin: 0 0 10px 0;"><strong>Precio:</strong> $<?php echo esc_html( number_format( $precio, 2 ) ); ?></p>
							<?php endif; ?>

							<?php if ( $telefono ) : ?>
								<p style="margin: 0;">
									<strong>Contacto:</strong>
									<a href="tel:<?php echo esc_attr( preg_replace('/[^0-9+]/', '', $telefono) ); ?>" style="color: #2e7d32; text-decoration: none; font-weight: bold;">
										<?php echo esc_html( $telefono ); ?>
									</a>
								</p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php the_content(); ?>

					<?php
					// Detalles específicos de Vehículos
					if ( has_term( 'vehiculos', 'categoria_anuncio' ) ) :
						$marca = get_field( 'marca' );
						$modelo = get_field( 'modelo' );
						$ano = get_field( 'ano_fabricacion' );
						$kilometraje = get_field( 'kilometraje' );
						$transmision = get_field( 'transmision' );
						$combustible = get_field( 'combustible' );
						$condicion = get_field( 'condicion' );

						// Verificar si al menos un campo existe antes de renderizar la caja
						if ( $marca || $modelo || $ano ) :
					?>
						<div class="detalles-vehiculo" style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 30px 0;">
							<h3 style="margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Especificaciones del Vehículo</h3>
							<ul style="list-style: none; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
								<?php if ( $condicion ) : ?><li><strong>Condición:</strong> <?php echo esc_html( $condicion ); ?></li><?php endif; ?>
								<?php if ( $marca ) : ?><li><strong>Marca:</strong> <?php echo esc_html( $marca ); ?></li><?php endif; ?>
								<?php if ( $modelo ) : ?><li><strong>Modelo:</strong> <?php echo esc_html( $modelo ); ?></li><?php endif; ?>
								<?php if ( $ano ) : ?><li><strong>Año:</strong> <?php echo esc_html( $ano ); ?></li><?php endif; ?>
								<?php if ( $kilometraje ) : ?><li><strong>Kilometraje:</strong> <?php echo esc_html( number_format( $kilometraje ) ); ?> Km</li><?php endif; ?>
								<?php if ( $transmision ) : ?><li><strong>Transmisión:</strong> <?php echo esc_html( $transmision ); ?></li><?php endif; ?>
								<?php if ( $combustible ) : ?><li><strong>Combustible:</strong> <?php echo esc_html( $combustible ); ?></li><?php endif; ?>
							</ul>
						</div>
					<?php
						endif;
					endif;
					?>

				</div><!-- .entry-content -->

				<footer class="entry-footer" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
					<p><strong><?php esc_html_e( 'Publicado el:', 'clasificados' ); ?></strong> <?php echo get_the_date(); ?></p>
				</footer><!-- .entry-footer -->
			</article><!-- #post-<?php the_ID(); ?> -->

			<?php
		endwhile; // End of the loop.
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
// get_sidebar();
get_footer();
