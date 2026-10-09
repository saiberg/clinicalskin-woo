<?php
/** Ajustes reales del gateway Webpay oficial para documentar los datos de prueba. */

$ajustes = (array) get_option( 'woocommerce_transbank_webpay_plus_rest_settings', array() );

foreach ( array_keys( $ajustes ) as $k ) {
	$valor = (string) $ajustes[ $k ];
	if ( stripos( $k, 'key' ) !== false || stripos( $k, 'token' ) !== false ) {
		$valor = substr( $valor, 0, 8 ) . str_repeat( '*', 12 );
	}
	echo $k . ' = ' . $valor . PHP_EOL;
}
