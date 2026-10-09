<?php
/**
 * Orígenes válidos que acepta la API de Starken (lista de comunas que usa
 * el plugin) y qué comuna guardó la tienda como origen.
 */

$datos = get_transient( 'wc_starken_origin' );
if ( ! $datos ) {
	$url  = defined( 'ARG_STARKEN_PLUGIN_API_URL' ) ? ARG_STARKEN_PLUGIN_API_URL : 'https://www.anyda.xyz/api/starken/';
	$resp = wp_remote_get( $url . 'cities' );
	$datos = json_decode( wp_remote_retrieve_body( $resp ), true );
}

echo 'tipo: ' . gettype( $datos ) . PHP_EOL;
if ( is_array( $datos ) ) {
	$claves = array_keys( $datos );
	echo 'total: ' . count( $claves ) . PHP_EOL;
	foreach ( array_slice( $claves, 0, 12, true ) as $k ) {
		echo '  ' . var_export( $k, true ) . ' => ' . var_export( $datos[ $k ], true ) . PHP_EOL;
	}
	$ajustes = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );
	$origen  = $ajustes['origin'] ?? '';
	echo 'origen guardado: ' . var_export( $origen, true ) . PHP_EOL;
	echo 'origen en lista: ' . ( array_key_exists( $origen, $datos ) ? 'si' : 'NO' ) . PHP_EOL;
} else {
	echo 'cuerpo: ' . mb_substr( (string) wp_remote_retrieve_body( $resp ?? array() ), 0, 400 ) . PHP_EOL;
}
