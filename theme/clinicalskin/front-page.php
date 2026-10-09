<?php
/**
 * Portada: <main> de index.html del sitio estático.
 */
get_header();
$p = cs_piezas();
?>
<?php if ( $p['main'] ) : ?>
<?php echo $p['main']; ?>
<?php else : ?>
<main class="container section">
<?php if ( have_posts() ) : ?>
<?php while ( have_posts() ) : the_post(); ?>
<article<?php post_class(); ?>>
	<h1><?php the_title(); ?></h1>
	<?php the_content(); ?>
</article>
<?php endwhile; ?>
<?php endif; ?>
</main>
<?php endif; ?>
<?php get_footer(); ?>
