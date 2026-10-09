<?php
/**
 * Página: usa el <main> del HTML estático si existe para el slug;
 * si no (páginas nativas de WP/WooCommerce), renderiza el contenido del editor.
 */
get_header();
$p = cs_piezas();
?>
<?php if ( $p['main'] ) : ?>
<?php echo $p['main']; ?>
<?php elseif ( cs_es_catalogo() ) : ?>
<main class="container">
<?php if ( have_posts() ) : ?>
<?php while ( have_posts() ) : the_post(); ?>
<article<?php post_class(); ?>>
	<?php cs_catalogo_abrir(); ?>
	<?php the_content(); ?>
	<?php cs_catalogo_cerrar(); ?>
</article>
<?php endwhile; ?>
<?php endif; ?>
</main>
<?php elseif ( function_exists( 'is_cart' ) && is_cart() ) : $n = cs_contador_carrito(); ?>
<main>
	<section class="page-header">
		<div class="container">
			<span class="eyebrow" data-cart-eyebrow>Tu carrito · <?php echo esc_html( $n ); ?> <?php echo 1 === $n ? 'producto' : 'productos'; ?></span>
			<h1>Revisa tu selección</h1>
			<p>Puedes comprar tu rutina completa o solo los productos que prefieras. Ajusta las cantidades antes de pagar.</p>
		</div>
	</section>

	<section class="section-tight">
		<div class="container">
			<div class="breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Inicio</a> / <a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Productos</a> / Carrito</div>
			<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
			<?php the_content(); ?>
			<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</section>

	<section class="section-tight">
		<div class="container">
			<div class="cta-banner">
				<div>
					<span class="eyebrow">¿Segura de tu rutina?</span>
					<h2>Repasa tu diagnóstico antes de comprar</h2>
				</div>
				<a href="<?php echo esc_url( home_url( '/resultado.html' ) ); ?>" class="btn btn-primary">Ver mi resultado</a>
			</div>
		</div>
	</section>
</main>
<?php elseif ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() && ! is_wc_endpoint_url( 'order-pay' ) ) : ?>
<main>
	<section class="page-header">
		<div class="container">
			<span class="eyebrow">Pago seguro</span>
			<h1>Finaliza tu compra</h1>
			<p>Confirma tus datos, elige cómo quieres recibir tu rutina y paga de forma segura.</p>
		</div>
	</section>

	<section class="section-tight">
		<div class="container">
			<div class="checkout-steps">
				<span class="cs done"><span class="cs-num">&#10003;</span> Carrito</span>
				<span class="cs active"><span class="cs-num">2</span> Datos y envío</span>
				<span class="cs"><span class="cs-num">3</span> Pago</span>
				<span class="cs"><span class="cs-num">4</span> Confirmación</span>
			</div>
			<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
			<?php the_content(); ?>
			<?php endwhile; ?>
			<?php endif; ?>
		</div>
	</section>
</main>
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
