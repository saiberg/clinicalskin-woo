<?php
/**
 * Comprobación del pedido pagado con Webpay en ambiente de integración:
 * estado, método de pago y que no se haya guardado ningún dato de tarjeta.
 */

$pedido = wc_get_order( 97 );
if ( ! $pedido ) {
	echo 'pedido 97 no existe' . PHP_EOL;
	return;
}
echo 'estado: ' . $pedido->get_status() . PHP_EOL;
echo 'metodo: ' . $pedido->get_payment_method() . ' | titulo: ' . $pedido->get_payment_method_title() . PHP_EOL;
echo 'total: ' . $pedido->get_total() . PHP_EOL;
echo 'envio: ' . $pedido->get_shipping_total() . ' (' . implode( ', ', array_map(
	fn( $m ) => $m->get_method_id() . ' ' . $m->get_name() . ' ' . $m->get_total(),
	array_values( $pedido->get_shipping_methods() )
) ) . ')' . PHP_EOL;
echo 'lineas: ' . count( $pedido->get_items() ) . PHP_EOL;
echo 'transaccion: ' . $pedido->get_transaction_id() . PHP_EOL;
$texto = serialize( $pedido->get_data() );
echo 'tarjeta_en_pedido: ' . ( preg_match( '/cs-tarjeta|cs-cvv|cs-venc|cs-titular|\b4\d{3}\s?\d{4}\s?\d{4}\s?\d{4}\b/', $texto ) ? 'SI' : 'no' ) . PHP_EOL;
