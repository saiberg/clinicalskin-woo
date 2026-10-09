<?php
/**
 * Muestra las paginas que usa WooCommerce (tienda, carrito, checkout, mi cuenta):
 * id, slug, titulo y el principio del contenido.
 * Uso: wp eval-file /tmp/cs/ver-paginas-woo.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ids = array(
	'shop'     => get_option( 'woocommerce_shop_page_id' ),
	'cart'     => get_option( 'woocommerce_cart_page_id' ),
	'checkout' => get_option( 'woocommerce_checkout_page_id' ),
	'cuenta'   => get_option( 'woocommerce_myaccount_page_id' ),
);

foreach ( $ids as $que => $id ) {
	$pagina = get_post( $id );
	if ( ! $pagina ) {
		echo "{$que}: (sin pagina)\n";
		continue;
	}
	$contenido = wp_strip_all_tags( $pagina->post_content );
	echo "{$que}: id={$pagina->ID} slug={$pagina->post_name} titulo={$pagina->post_title}\n";
	echo '  bloques=' . ( has_blocks( $pagina->post_content ) ? 'si' : 'no' ) . "\n";
	echo '  contenido=' . mb_substr( preg_replace( '/\s+/', ' ', $contenido ), 0, 160 ) . "\n";
}
