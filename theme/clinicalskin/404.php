<?php
/** Página no encontrada. */
get_header();
?>
<main class="container section">
	<span class="eyebrow">Error 404</span>
	<h1>No encontramos esa página</h1>
	<p class="lead">Puede que el enlace esté desactualizado. Vuelve al inicio o repasa nuestro catálogo.</p>
	<p><a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">Ir al inicio</a></p>
</main>
<?php get_footer(); ?>
