<?php
/**
 * Chilexpress: precarga el cache local de comunas de las 16 regiones.
 *
 * Sin este cache, cada cambio de region en el checkout espera la llamada a la
 * API de Chilexpress (varios segundos); con el archivo local la lista de
 * comunas aparece de inmediato.
 *
 * Uso: docker compose -f C:\docker\clinicalskin-woo\docker-compose.yml run --rm cli wp eval-file /tmp/cs/chilexpress-comunas-cache.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cobertura = new Chilexpress_Woo_Oficial_Coverage();
$regiones  = array( 'R1', 'R2', 'R3', 'R4', 'R5', 'R6', 'R7', 'R8', 'R9', 'R10', 'R11', 'R12', 'R14', 'R15', 'R16', 'RM' );

foreach ( $regiones as $codigo ) {
	$inicio  = microtime( true );
	$comunas = $cobertura->obtener_comunas( $codigo );
	$ms      = (int) round( ( microtime( true ) - $inicio ) * 1000 );
	echo $codigo . ': ' . count( $comunas ) . ' comunas (' . $ms . ' ms)' . PHP_EOL;
}

echo 'cache de comunas listo' . PHP_EOL;
