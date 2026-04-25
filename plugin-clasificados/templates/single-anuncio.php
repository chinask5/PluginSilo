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
					the_content();
					?>
				</div><!-- .entry-content -->

				<footer class="entry-footer" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">
					<!-- Espacio para ACF o detalles adicionales de contacto -->
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
