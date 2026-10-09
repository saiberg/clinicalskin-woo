<?php
/**
 * Limpieza tras las pruebas: borra los pedidos de prueba (89, 93 y 95) y
 * vacía el carrito para dejar la tienda en estado limpio.
 */
foreach ( array( 89, 93, 95 ) as $id ) {
	$pedido = wc_get_order( $id );
	if ( $pedido ) {
		echo 'pedido ' . $id . ': ' . $pedido->get_status() . ' -> ' . ( $pedido->delete( true ) ? 'borrado' : 'no borrado' ) . PHP_EOL;
	} else {
		echo 'pedido ' . $id . ': no existe' . PHP_EOL;
	}
}

if ( function_exists( 'WC' ) && WC()->cart ) {
	WC()->cart->empty_cart();
	echo 'carrito vaciado' . PHP_EOL;
}
