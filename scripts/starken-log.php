<?php
/**
 * Últimas líneas del log del plugin de Starken para ver por qué la cotización
 * cayó al costo de respaldo.
 */

$archivos = glob( WC_LOG_DIR . 'starken-pro*' );
$mas_reciente = null;
$mas_nuevo = 0;
foreach ( (array) $archivos as $f ) {
	$t = filemtime( $f );
	if ( $t >= $mas_nuevo ) {
		$mas_nuevo = $t;
		$mas_reciente = $f;
	}
}
if ( ! $mas_reciente ) {
	echo '(sin log)' . PHP_EOL;
	return;
}
echo 'archivo: ' . basename( $mas_reciente ) . PHP_EOL;
$lineas = array_values( array_filter( explode( "\n", (string) file_get_contents( $mas_reciente ) ) ) );
foreach ( array_slice( $lineas, -25 ) as $l ) {
	echo mb_substr( $l, 0, 400 ) . PHP_EOL;
}
