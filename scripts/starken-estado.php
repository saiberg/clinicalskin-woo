<?php
/**
 * Estado real de la zona 1 tras crear la instancia de Starken: qué métodos
 * quedó habilitados, qué opciones guardó el plano para cada uno y si el
 * plugin escribió algo en su log de cotizaciones.
 */

echo '--- flat_rate_2 ---' . PHP_EOL;
var_export( get_option( 'woocommerce_flat_rate_2_settings' ) );
echo PHP_EOL;

echo '--- zona 1 ---' . PHP_EOL;
$zona = new WC_Shipping_Zone( 1 );
foreach ( $zona->get_shipping_methods( false ) as $m ) {
	echo '  ' . $m->get_instance_id() . ' ' . $m->id
		. ' | enabled_opt=' . var_export( $m->get_option( 'enabled' ), true )
		. ' | is_enabled=' . ( $m->is_enabled() ? 'si' : 'no' )
		. ' | ' . $m->get_title() . PHP_EOL;
}

echo '--- starken instancia ---' . PHP_EOL;
$st = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_5_settings', array() );
foreach ( array( 'enabled', 'title', 'origin', 'api', 'descripcion', 'enable_fallback', 'fallback_cost' ) as $k ) {
	$vs = isset( $st[ $k ] ) ? (string) $st[ $k ] : '(sin clave)';
	if ( 'api' === $k ) {
		$vs = substr( $vs, 0, 8 ) . '…';
	}
	echo '  ' . $k . ' = ' . $vs . PHP_EOL;
}

echo '--- log starken ---' . PHP_EOL;
$archivos = glob( WC_LOG_DIR . 'starken-pro*' );
echo '  ' . ( $archivos ? implode( ', ', $archivos ) : '(sin log)' ) . PHP_EOL;
foreach ( (array) $archivos as $f ) {
	$lineas = array_slice( array_filter( explode( "\n", (string) file_get_contents( $f ) ) ), -6 );
	foreach ( $lineas as $l ) {
		echo '  ' . mb_substr( $l, 0, 240 ) . PHP_EOL;
	}
}
