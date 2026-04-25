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
							<?php the_title( '<h2 class="entry-title" style="font-size: 1.2em;"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
						</header><!-- .entry-header -->

						<div class="entry-summary">
							<?php the_excerpt(); ?>
						</div><!-- .entry-summary -->

						<footer class="entry-footer">
							<a href="<?php the_permalink(); ?>" class="button" style="display: inline-block; padding: 5px 10px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 3px;">Ver detalles</a>
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
