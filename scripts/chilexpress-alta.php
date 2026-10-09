<?php
/**
 * Chilexpress (plugin oficial): alta en ambiente de prueba.
 * 1) Prueba las dos llaves en la API de cobertura y guarda la que funcione.
 * 2) Puebla regiones/comunas habilitadas desde la API.
 * 3) Guarda datos de la tienda (comuna origen, TCC, medidas por defecto).
 * 4) Cotiza Providencia y Calama para comprobar el Rating.
 */

$primaria    = '9a72fa21133747538182a6edf71e82f3';
$secundaria  = 'f9b8b65bf2a44dd6bc910915b4e58f6d';
$base_qa     = 'https://qaservices.wschilexpress.com/';

function cs_sondeo( $base, $clave ) {
	$r = wp_remote_get( $base . 'georeference/v2/api/v2.0/regions', array(
		'headers' => array(
			'Ocp-Apim-Subscription-Key' => $clave,
			'Accept'                    => 'application/json',
		),
		'timeout' => 20,
	) );
	if ( is_wp_error( $r ) ) {
		return 'error ' . $r->get_error_message();
	}
	$cod = (int) wp_remote_retrieve_response_code( $r );
	return $cod . ' ' . substr( wp_remote_retrieve_body( $r ), 0, 120 );
}

/* 1) Sondeo de llaves contra cobertura */
$geo_prim = cs_sondeo( $base_qa, $primaria );
$geo_sec  = cs_sondeo( $base_qa, $secundaria );
echo 'cobertura primaria   : ' . $geo_prim . PHP_EOL;
echo 'cobertura secundaria : ' . $geo_sec . PHP_EOL;

$clave_geo = '';
if ( 0 === strpos( $geo_prim, '200' ) ) {
	$clave_geo = $primaria;
} elseif ( 0 === strpos( $geo_sec, '200' ) ) {
	$clave_geo = $secundaria;
}
if ( '' === $clave_geo ) {
	echo 'NINGUNA llave sirve para cobertura; detenido.' . PHP_EOL;
	return;
}
echo 'llave de cobertura: ' . substr( $clave_geo, 0, 8 ) . '...' . PHP_EOL;

/* 2) Modulos: ambiente de prueba + llaves */
$mod = (array) get_option( 'chilexpress_woo_oficial', array() );
$mod['ambiente']                      = 'test';
$mod['api_key_cotizador_value']       = $primaria;
$mod['api_key_georeferencia_value']   = $clave_geo;
$mod['api_key_generacion_ot_value']   = $primaria;
$mod['api_key_cotizador_enabled']     = '1';
$mod['api_key_georeferencia_enabled'] = '1';
$mod['api_key_generacion_ot_enabled'] = '1';
update_option( 'chilexpress_woo_oficial', $mod );
echo 'ambiente: ' . $mod['ambiente'] . PHP_EOL;

$api      = new Chilexpress_Woo_Oficial_API();
$regiones = $api->obtener_regiones();
if ( is_wp_error( $regiones ) || empty( $regiones ) || ! is_array( $regiones ) ) {
	echo 'regiones: FALLA ' . ( is_wp_error( $regiones ) ? $regiones->get_error_message() : '(vacio)' ) . PHP_EOL;
	return;
}
echo 'regiones: ' . count( $regiones ) . ' (' . implode( ' | ', array_slice( array_values( $regiones ), 0, 3 ) ) . ')' . PHP_EOL;

/* Comunas de todas las regiones */
$comunas        = array();
$comuna_santiago = '';
$region_santiago = '';
foreach ( array_keys( $regiones ) as $rid ) {
	$lista = $api->obtener_comunas_desde_region( $rid );
	if ( is_wp_error( $lista ) || empty( $lista ) || ! is_array( $lista ) ) {
		echo '  comunas ' . $rid . ': FALLA' . PHP_EOL;
		continue;
	}
	foreach ( $lista as $nombre => $codigo ) {
		$comunas[ $nombre ] = $codigo;
	}
}
/* Origen: la API nombra la comuna "SANTIAGO CENTRO" (codigo STGO) */
foreach ( $comunas as $nombre => $codigo ) {
	if ( 0 === strcasecmp( trim( (string) $nombre ), 'SANTIAGO CENTRO' ) ) {
		$comuna_santiago = $codigo;
	}
}
if ( '' === $comuna_santiago ) {
	foreach ( $comunas as $nombre => $codigo ) {
		if ( false !== stripos( (string) $nombre, 'SANTIAGO' ) ) {
			$comuna_santiago = $codigo;
		}
	}
}
foreach ( $regiones as $rid => $rnombre ) {
	$lista = $api->obtener_comunas_desde_region( $rid );
	if ( is_array( $lista ) ) {
		foreach ( $lista as $nombre => $codigo ) {
			if ( $codigo === $comuna_santiago ) {
				$region_santiago = $rid;
			}
		}
	}
}
/* Alias: el checkout escribe la comuna "SANTIAGO" */
if ( '' !== $comuna_santiago && ! isset( $comunas['SANTIAGO'] ) ) {
	$comunas['SANTIAGO'] = $comuna_santiago;
}
echo 'comunas: ' . count( $comunas ) . ' | origen: ' . $region_santiago . ' / ' . $comuna_santiago . PHP_EOL;
if ( count( $comunas ) < 200 || '' === $comuna_santiago ) {
	echo 'catalogo incompleto; detenido.' . PHP_EOL;
	return;
}

/* Codigos de estado de WooCommerce (CL-RM, CL-AN...) para que el metodo
 * reconozca el destino del paquete, mas los codigos propios de Chilexpress. */
$estados_wc = array();
if ( function_exists( 'WC' ) && WC()->countries ) {
	$estados_wc = array_keys( (array) WC()->countries->get_states( 'CL' ) );
}
echo 'estados WC: ' . count( $estados_wc ) . ' (ej. ' . implode( ', ', array_slice( $estados_wc, 0, 3 ) ) . ')' . PHP_EOL;

update_option( 'chilexpress_woo_oficial_region_comuna', array(
	'regiones_habilitadas' => array_values( array_unique( array_merge( $estados_wc, array_keys( $regiones ) ) ) ),
	'comunas_habilitadas'  => $comunas,
) );
echo 'catalogo guardado.' . PHP_EOL;

/* 3) Datos de la tienda */
$gen = (array) get_option( 'chilexpress_woo_oficial_general', array() );
$gen['region_origen']            = $region_santiago;
$gen['comuna_origen']            = $comuna_santiago;
$gen['numero_tcc_origen']        = '18578680';
$gen['tipo_prioridad']           = 2;
$gen['porcentaje_descuento']     = 0;
$gen['ancho_producto_defecto']   = 15;
$gen['alto_producto_defecto']    = 10;
$gen['largo_producto_defecto']   = 20;
$gen['peso_producto_defecto']    = 0.5;
$gen['dias_procesamiento']       = 1;
$gen['articulos_tienda']         = 5;
$gen['corte_horario']            = 12;
$gen['dias_semana']              = array( 1, 2, 3, 4, 5 );
update_option( 'chilexpress_woo_oficial_general', $gen );
echo 'datos de tienda guardados (TCC ' . $gen['numero_tcc_origen'] . ').' . PHP_EOL;

/* 4) Cotizacion de prueba */
function cs_cotiza( $api, $origen, $destino ) {
	$resp = $api->obtener_cotizacion( $origen, $destino, 2, '18578680', 0.5, 10, 15, 20, 10000 );
	if ( is_wp_error( $resp ) ) {
		return 'FALLA ' . $resp->get_error_message();
	}
	if ( empty( $resp ) || ! is_array( $resp ) ) {
		return 'FALLA (sin datos)';
	}
	$lineas = array();
	foreach ( $resp as $s ) {
		$lineas[] = $s->serviceTypeCode . ' ' . $s->serviceDescription . ' $' . number_format( $s->serviceValueDiscount, 0, ',', '.' );
	}
	return implode( ' || ', $lineas );
}

$destinos = array();
foreach ( array( 'Providencia', 'Calama' ) as $buscada ) {
	foreach ( $comunas as $nombre => $codigo ) {
		if ( 0 === strcasecmp( trim( (string) $nombre ), $buscada ) ) {
			$destinos[ $buscada ] = $codigo;
		}
	}
}
echo 'destinos: ' . json_encode( $destinos ) . PHP_EOL;
foreach ( $destinos as $nombre => $codigo ) {
	echo 'cotizacion ' . $nombre . ' (' . $codigo . '): ' . cs_cotiza( $api, $comuna_santiago, $codigo ) . PHP_EOL;
}

/* Si la llave primaria no cotiza, probamos la secundaria */
if ( isset( $destinos['Providencia'] ) ) {
	$resp = $api->obtener_cotizacion( $comuna_santiago, $destinos['Providencia'], 2, '18578680', 0.5, 10, 15, 20, 10000 );
	if ( is_wp_error( $resp ) || empty( $resp ) ) {
		echo 'reintento con llave secundaria en el cotizador...' . PHP_EOL;
		$mod['api_key_cotizador_value'] = $secundaria;
		update_option( 'chilexpress_woo_oficial', $mod );
		$api2 = new Chilexpress_Woo_Oficial_API();
		echo 'cotizacion Providencia (secundaria): ' . cs_cotiza( $api2, $comuna_santiago, $destinos['Providencia'] ) . PHP_EOL;
	}
}
echo 'FIN' . PHP_EOL;
