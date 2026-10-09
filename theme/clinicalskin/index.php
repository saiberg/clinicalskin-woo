<?php
/**
 * Respaldo (archivos, entradas, cualquier consulta sin plantilla propia).
 */
get_header();
?>
<main class="container section">
<?php if ( cs_es_catalogo() ) : ?>
	<?php if ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
	<article<?php post_class(); ?>>
		<?php cs_catalogo_abrir(); ?>
		<?php the_content(); ?>
		<?php cs_catalogo_cerrar(); ?>
	</article>
	<?php endwhile; ?>
	<?php endif; ?>
<?php elseif ( have_posts() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
	<article<?php post_class(); ?>>
		<h1><?php the_title(); ?></h1>
		<?php the_content(); ?>
	</article>
	<?php endwhile; ?>
<?php else : ?>
	<h1>Sin contenido</h1>
<?php endif; ?>
</main>
<?php get_footer(); ?>
