<?php
/**
 * Plantilla para el listado (Archive) de Anuncios y SEO Silos.
 * Compatible con GeneratePress o temas estándar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<?php
				// Breadcrumbs
				if ( class_exists( 'Clasificados_SEO' ) ) {
					echo Clasificados_SEO::get_breadcrumbs();
				}

				// H1 Dinámico usando la clase SEO
				if ( class_exists( 'Clasificados_SEO' ) ) {
					$h1 = Clasificados_SEO::get_h1();
					echo '<h1 class="page-title">' . $h1 . '</h1>';
				} else {
					the_archive_title( '<h1 class="page-title">', '</h1>' );
				}
				the_archive_description( '<div class="taxonomy-description">', '</div>' );
				?>
			</header><!-- .page-header -->

			<div class="clasificados-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'clasificado-item' ); ?> style="border: 1px solid #eee; padding: 15px; border-radius: 5px;">

						<?php if ( has_post_thumbnail() ) : ?>
							<div class="anuncio-thumbnail" style="margin-bottom: 10px;">
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'medium', array( 'style' => 'width:100%; height:auto;' ) ); ?>
								</a>
							</div>
						<?php endif; ?>

						<header class="entry-header">
							<?php
							$precio = get_field( 'precio' );
							if ( $precio ) {
								echo '<div style="font-weight: bold; color: #2e7d32; font-size: 1.1em; margin-bottom: 5px;">$' . esc_html( number_format( $precio, 2 ) ) . '</div>';
							}
							the_title( '<h2 class="entry-title" style="font-size: 1.2em; margin: 0 0 10px 0;"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark" style="text-decoration: none; color: #333;">', '</a></h2>' );
							?>
						</header><!-- .entry-header -->

						<div class="entry-summary" style="font-size: 0.9em; color: #666; margin-bottom: 15px;">
							<?php
							// Si es vehículo, mostrar resumen rápido
							if ( has_term( 'vehiculos', 'categoria_anuncio' ) ) {
								$ano = get_field( 'ano_fabricacion' );
								$kilometraje = get_field( 'kilometraje' );
								$transmision = get_field( 'transmision' );

								$detalles = array();
								if ( $ano ) $detalles[] = $ano;
								if ( $kilometraje ) $detalles[] = number_format($kilometraje) . ' Km';
								if ( $transmision ) $detalles[] = $transmision;

								if ( !empty($detalles) ) {
									echo '<p style="margin:0 0 10px 0;">' . esc_html( implode( ' • ', $detalles ) ) . '</p>';
								}
							}

							// Si es mascota, mostrar resumen rápido
							if ( has_term( 'mascotas', 'categoria_anuncio' ) ) {
								$raza = get_field( 'raza' );
								$edad = get_field( 'edad' );

								$detalles = array();
								if ( $raza ) $detalles[] = $raza;
								if ( $edad ) $detalles[] = $edad;

								if ( !empty($detalles) ) {
									echo '<p style="margin:0 0 10px 0;">' . esc_html( implode( ' • ', $detalles ) ) . '</p>';
								}
							}

							// Mostrar extracto corto
							echo wp_trim_words( get_the_excerpt(), 15, '...' );
							?>
						</div><!-- .entry-summary -->

						<footer class="entry-footer" style="margin-top: auto;">
							<a href="<?php the_permalink(); ?>" class="button" style="display: block; text-align: center; padding: 8px 10px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 3px;">Ver detalles</a>
						</footer><!-- .entry-footer -->
					</article><!-- #post-<?php the_ID(); ?> -->
					<?php
				endwhile;
				?>
			</div><!-- .clasificados-grid -->

			<?php
			the_posts_pagination( array(
				'prev_text' => __( 'Anterior', 'clasificados' ),
				'next_text' => __( 'Siguiente', 'clasificados' ),
			) );

		else :

			echo '<p>' . esc_html__( 'No se encontraron anuncios para esta ubicación y categoría.', 'clasificados' ) . '</p>';

		endif;
		?>

	</main><!-- #main -->
</div><!-- #primary -->

<?php
// Opcional: Cargar sidebar si el tema lo soporta
// get_sidebar();
get_footer();
