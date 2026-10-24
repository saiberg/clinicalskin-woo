<?php
/* Listado: id | tipo | marca | precio | nombre (para remapear el diagnostico). */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$marcas = array( 'AVENE', 'EUCERIN', 'ISDIN', 'LRP', 'URIAGE', 'VICHY' );
$ids    = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => 'publish' ) );
usort(
	$ids,
	function ( $a, $b ) {
		return 0;
	}
);
foreach ( $ids as $id ) {
	$p    = wc_get_product( $id );
	$cats = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'names' ) );
	$tipo = '';
	$marca = '';
	foreach ( $cats as $c ) {
		if ( in_array( $c, $marcas, true ) ) {
			$marca = $c;
		} else {
			$tipo = $c;
		}
	}
	printf( "%d | %-18s | %-7s | %6s | %s | %s\n", $id, $tipo, $marca, $p->get_regular_price(), $p->get_sku(), $p->get_name() );
}
