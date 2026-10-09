<?php
/**
 * Carrito vacío con el formato de .cart-empty de carrito.html.
 *
 * Se mantiene la salida nativa de "woocommerce_cart_is_empty" (avisos y el
 * contenedor .wc-empty-cart-message) porque el JS de WooCommerce usa ese
 * contenedor para sustituir el carrito cuando se elimina el último artículo;
 * woo.css lo oculta y aquí se imprime la tarjeta del diseño.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked woocommerce_output_all_notices - 5
 * @hooked wc_empty_cart_message          - 10 ( retirado: ver functions.php )
 */
do_action( 'woocommerce_cart_is_empty' );
?>

<!-- Ancla que usa el JS de WooCommerce al vaciar el carrito por AJAX. -->
<div class="wc-empty-cart-message" style="display: none;"></div>

<div class="cart-empty is-visible">
	<h3><?php esc_html_e( 'Tu carrito está vacío', 'clinicalskin' ); ?></h3>
	<p><?php esc_html_e( 'Haz el diagnóstico y arma tu rutina personalizada en menos de 3 minutos.', 'clinicalskin' ); ?></p>
	<div class="actions-row">
		<a href="<?php echo esc_url( home_url( '/diagnostico.html' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Comenzar diagnóstico', 'clinicalskin' ); ?></a>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Ver productos', 'clinicalskin' ); ?></a>
	</div>
</div>
