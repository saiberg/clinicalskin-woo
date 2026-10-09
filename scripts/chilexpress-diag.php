<?php
$api      = new Chilexpress_Woo_Oficial_API();
$regiones = $api->obtener_regiones();
$comunas  = array();
foreach ( array_keys( $regiones ) as $rid ) {
	$lista = $api->obtener_comunas_desde_region( $rid );
	$n     = is_array( $lista ) ? count( $lista ) : 0;
	echo $rid . ': ' . $n . PHP_EOL;
	if ( is_array( $lista ) ) {
		foreach ( $lista as $nombre => $codigo ) {
			$comunas[ $nombre ] = $codigo;
		}
	}
}
echo 'totalunicas: ' . count( $comunas ) . PHP_EOL;
$hit = array();
foreach ( $comunas as $nombre => $codigo ) {
	if ( false !== stripos( (string) $nombre, 'santiago' ) || false !== stripos( (string) $nombre, 'providencia' ) || false !== stripos( (string) $nombre, 'calama' ) ) {
		$hit[] = $nombre . '=' . $codigo;
	}
}
echo 'coincidencias: ' . implode( ' | ', $hit ) . PHP_EOL;
$muestra = array_slice( $comunas, 0, 12, true );
echo 'muestra: ' . json_encode( $muestra ) . PHP_EOL;
