<?php
/**
 * Comprobación de la prueba de pago: estado y contenido del pedido 89,
 * y si la tienda guardó algún dato de tarjeta.
 */
$pedido = wc_get_order( 89 );
if ( ! $pedido ) {
	echo 'pedido 89 no existe' . PHP_EOL;
	return;
}
echo 'estado: ' . $pedido->get_status() . PHP_EOL;
echo 'metodo: ' . $pedido->get_payment_method() . PHP_EOL;
echo 'titulo_metodo: ' . $pedido->get_payment_method_title() . PHP_EOL;
echo 'total: ' . $pedido->get_total() . PHP_EOL;
echo 'lineas: ' . count( $pedido->get_items() ) . PHP_EOL;
$notas = $pedido->get_customer_note();
if ( method_exists( $pedido, 'get_formatted_order_notes' ) ) {
	$notas .= ' ' . wp_strip_all_tags( $pedido->get_formatted_order_notes() );
}
echo 'notas: ' . str_replace( PHP_EOL, ' / ', $notas ) . PHP_EOL;

/* Ningún dato de tarjeta debe aparecer en el pedido. */
$texto = serialize( $pedido->get_data() );
echo 'tarjeta_en_pedido: ' . ( preg_match( '/cs-tarjeta|cs-cvv|cs-venc|cs-titular|\b4\d{3}\s?\d{4}\s?\d{4}\s?\d{4}\b/', $texto ) ? 'SI' : 'no' ) . PHP_EOL;
