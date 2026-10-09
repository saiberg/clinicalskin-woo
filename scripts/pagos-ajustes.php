<?php
/**
 * Pagos: se desactivan las maquetas Khipu y Mercado Pago y los gateways
 * nativos transferencia (bacs) y contra entrega (cod). Solo sigue Webpay.
 */

$desactivar = array( 'khipu', 'mercado', 'bacs', 'cod' );
foreach ( $desactivar as $id ) {
	$clave  = 'woocommerce_' . $id . '_settings';
	$ajustes = (array) get_option( $clave, array() );
	$ajustes['enabled'] = 'no';
	update_option( $clave, $ajustes );
	echo $id . ' -> enabled=' . $ajustes['enabled'] . PHP_EOL;
}

/* Borrado del pedido de prueba de Webpay. */
$pedido = wc_get_order( 97 );
if ( $pedido ) {
	$pedido->delete( true );
	echo 'pedido 97 borrado' . PHP_EOL;
} else {
	echo 'pedido 97: no existe' . PHP_EOL;
}

/* Gateways disponibles de verdad en el contexto de tienda. */
if ( function_exists( 'WC' ) && WC()->payment_gateways() ) {
	$disponibles = WC()->payment_gateways()->get_available_payment_gateways();
	echo 'disponibles: ' . ( $disponibles ? implode( ', ', array_keys( $disponibles ) ) : '(ninguno)' ) . PHP_EOL;
}
echo 'orden: ' . json_encode( get_option( 'woocommerce_gateway_order' ) ) . PHP_EOL;
