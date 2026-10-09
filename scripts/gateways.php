<?php
/**
 * Estado de las pasarelas después de instalar el plugin de Transbank:
 * cuáles hay, cuáles están activas y qué entorno/credenciales usa Webpay.
 */

WC()->payment_gateways();
$lista = WC()->payment_gateways()->payment_gateways();

echo 'pasarelas:' . PHP_EOL;
foreach ( $lista as $id => $g ) {
	echo '  ' . $id . ' | enabled=' . $g->enabled . ' | titulo=' . $g->get_title() . PHP_EOL;
}

foreach ( array( 'transbank_webpay_plus_rest', 'transbank_oneclick_mall_rest' ) as $id ) {
	if ( empty( $lista[ $id ] ) ) {
		continue;
	}
	$g = $lista[ $id ];
	echo '--- ' . $id . ' ---' . PHP_EOL;
	foreach ( $g->settings as $clave => $valor ) {
		/* Las credenciales de integración de Transbank son públicas; se igual. */
		echo '  ' . $clave . ' = ' . $valor . PHP_EOL;
	}
	if ( method_exists( $g, 'get_environment' ) ) {
		echo '  get_environment() = ' . $g->get_environment() . PHP_EOL;
	}
	echo '  instructions = ' . $g->get_instructions() . PHP_EOL;
}

echo 'orden_gateway: ' . get_option( 'woocommerce_gateway_order', '(sin definir)' ) . PHP_EOL;
