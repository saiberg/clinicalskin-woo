<?php
/**
 * Verifica que los 11 productos importados coincidan con el CSV del sitio estatico:
 * nombre, precio, categoria, atributos, meta y foto.
 *
 * Uso: wp eval-file /tmp/cs/verificar-catalogo.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$origen = ABSPATH . 'static/woocommerce/catalogo-clinical-skin.csv';
$handle = fopen( $origen, 'r' );
if ( ! $handle ) {
	echo "ERROR: no se lee {$origen}\n";
	exit( 1 );
}

$cabecera = fgetcsv( $handle );
$col      = array_flip( $cabecera );
$errores  = 0;
$total    = 0;

while ( ( $fila = fgetcsv( $handle ) ) !== false ) {
	++$total;
	$sku  = $fila[ $col['SKU'] ];
	$prod = wc_get_product( wc_get_product_id_by_sku( $sku ) );

	if ( ! $prod ) {
		echo "FALTA producto {$sku}\n";
		++$errores;
		continue;
	}

	$comprobar = array(
		'nombre'  => array( $prod->get_name(), $fila[ $col['Name'] ] ),
		'precio'  => array( (string) $prod->get_regular_price(), (string) $fila[ $col['Regular price'] ] ),
		'desc'    => array( $prod->get_short_description(), $fila[ $col['Short description'] ] ),
		'ficha'   => array( $prod->get_description(), $fila[ $col['Description'] ] ),
		'paso'    => array( $prod->get_meta( '_cs_paso' ), $fila[ $col['Meta: _cs_paso'] ] ),
		'usos'    => array( $prod->get_meta( '_cs_modo_uso' ), $fila[ $col['Meta: _cs_modo_uso'] ] ),
		'ingre'   => array( $prod->get_meta( '_cs_ingredientes' ), $fila[ $col['Meta: _cs_ingredientes'] ] ),
		'foto'    => array( (string) $prod->get_image_id(), '' ), // solo pide que tenga foto
	);
	foreach ( $comprobar as $que => $par ) {
		list( $real, $esperado ) = $par;
		if ( 'foto' === $que ) {
			if ( ! $real ) {
				echo "FALTA foto en {$sku}\n";
				++$errores;
			}
			continue;
		}
		if ( $real !== $esperado ) {
			echo "DIFIERE {$sku} [{$que}]: '" . mb_substr( $real, 0, 40 ) . "' != '" . mb_substr( $esperado, 0, 40 ) . "'\n";
			++$errores;
		}
	}

	/* categoria */
	$cats = wp_get_post_terms( $prod->get_id(), 'product_cat', array( 'fields' => 'names' ) );
	if ( ! in_array( $fila[ $col['Categories'] ], (array) $cats, true ) ) {
		echo "DIFIERE {$sku} [categoria]: " . implode( ',', (array) $cats ) . " != " . $fila[ $col['Categories'] ] . "\n";
		++$errores;
	}

	/* atributos globales: posicion 1=Paso, 2=Tipo de piel, 3=Preocupacion, 4=Intensidad */
	$attrs = $prod->get_attributes();
	foreach ( array( 1 => 'pa_paso', 2 => 'pa_tipo-de-piel', 3 => 'pa_preocupacion', 4 => 'pa_intensidad' ) as $nro => $tax ) {
		$esperado = array_map( 'trim', explode( ',', $fila[ $col[ 'Attribute ' . $nro . ' value(s)' ] ] ) );
		sort( $esperado );
		$reales = isset( $attrs[ $tax ] ) ? (array) wp_get_object_terms( $prod->get_id(), $tax, array( 'fields' => 'names' ) ) : array();
		sort( $reales );
		if ( $esperado !== $reales ) {
			echo "DIFIERE {$sku} [{$tax}]: " . implode( ',', $reales ) . " != " . implode( ',', $esperado ) . "\n";
			++$errores;
		}
	}
}
fclose( $handle );

$publicados = count( wc_get_products( array( 'limit' => -1, 'status' => 'publish' ) ) );
echo "Filas CSV: {$total} | Publicados: {$publicados} | Errores: {$errores}\n";
echo $errores ? "REVISAR\n" : "CATALOGO OK\n";
