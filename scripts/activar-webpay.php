<?php
/**
 * Activa el Webpay oficial de Transbank en ambiente de integración (pruebas)
 * y lo acomoda en la fila del checkout: primero, con el título del diseño y
 * sin la descripción larga (el subtítulo y la nota los pone el tema).
 *
 * La maqueta "webpay" se desactiva para que no aparezca dos veces la fila.
 */

$tbk = get_option( 'woocommerce_transbank_webpay_plus_rest_settings', array() );
$tbk['enabled' ]                     = 'yes';
$tbk['environment']                  = 'TEST'; /* Integración: sin dinero real. */
$tbk['payment_gateway_description']  = '';
$tbk['after_payment_order_status']   = 'processing';
update_option( 'woocommerce_transbank_webpay_plus_rest_settings', $tbk );

/* Oneclick no aplica: es para inscribir tarjetas. */
$oneclick = get_option( 'woocommerce_transbank_oneclick_mall_rest_settings', array() );
$oneclick['enabled'] = 'no';
update_option( 'woocommerce_transbank_oneclick_mall_rest_settings', $oneclick );

/* La maqueta de tarjeta deja de ser un método: su fila la usa el gateway real. */
$maqueta = get_option( 'woocommerce_webpay_settings', array() );
$maqueta['enabled'] = 'no';
update_option( 'woocommerce_webpay_settings', $maqueta );

/* Orden de las filas del diseño: WooCommerce espera un array (no un JSON). */
update_option(
	'woocommerce_gateway_order',
	array(
		'transbank_webpay_plus_rest' => 0,
		'khipu'                      => 1,
		'mercado'                    => 2,
		'bacs'                       => 8,
		'cod'                        => 9,
	)
);

echo 'entorno: ' . $tbk['environment'] . PHP_EOL;
echo 'estado: ' . $tbk['enabled'] . PHP_EOL;
echo 'orden: ' . implode( ',', array_keys( (array) get_option( 'woocommerce_gateway_order' ) ) ) . PHP_EOL;
