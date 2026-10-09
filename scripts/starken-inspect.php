<?php
/**
 * Estado del plugin de Starken tras instalarlo: ajustes guardados, campos de
 * configuración que ofrece y métodos de envío que registra en WooCommerce.
 */

$ajustes = get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );
echo '--- ajustes ---' . PHP_EOL;
if ( ! $ajustes ) {
	echo '(sin guardar aún)' . PHP_EOL;
}
foreach ( (array) $ajustes as $k => $v ) {
	$vs = is_scalar( $v ) ? (string) $v : wp_json_encode( $v );
	if ( strlen( $vs ) > 44 ) {
		$vs = substr( $vs, 0, 14 ) . '…' . substr( $vs, -4 );
	}
	echo '  ' . $k . ' = ' . $vs . PHP_EOL;
}
echo 'verificado: ' . get_option( 'wc_starken_connection_verified', 'no' ) . PHP_EOL;

$fichero = WP_PLUGIN_DIR . '/calculo-de-despacho-via-starken-para-woocommerce/calculo-de-despacho-via-starken-para-woocommerce.php';
$src     = file_get_contents( $fichero );
preg_match_all( "/'id'\s*=>\s*'([^']+)'/", $src, $m1 );
preg_match_all( "/'title'\s*=>\s*'([^']+)'/", $src, $m2 );
echo '--- campos ---' . PHP_EOL;
echo '  ids: ' . implode( ', ', array_unique( $m1[1] ) ) . PHP_EOL;
echo '  titulos: ' . implode( ' | ', array_unique( $m2[1] ) ) . PHP_EOL;

echo '--- metodos registrados ---' . PHP_EOL;
WC()->shipping();
$metodos = WC()->shipping()->get_shipping_methods();
foreach ( $metodos as $id => $m ) {
	echo '  ' . $id . ' = ' . $m->get_title() . PHP_EOL;
}

$zona = WC_Shipping_Zones::get_zone( 1 );
echo '--- zona 1 ---' . PHP_EOL;
foreach ( $zona->get_shipping_methods( true ) as $m ) {
	echo '  ' . $m->get_id() . ' | habilitado=' . ( $m->is_enabled() ? 'si' : 'no' ) . ' | ' . $m->get_title() . PHP_EOL;
}
