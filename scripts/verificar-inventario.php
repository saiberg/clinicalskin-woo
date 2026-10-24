<?php
/* Verificacion rapida del inventario importado. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ids = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => 'publish' ) );
printf( "Publicados: %d\n", count( $ids ) );

$con_imagen = 0;
$sin_imagen = array();
foreach ( $ids as $id ) {
	$p = wc_get_product( $id );
	if ( $p->get_image_id() ) {
		$con_imagen++;
	} else {
		$sin_imagen[] = $p->get_name();
	}
}
printf( "Con imagen: %d | Sin imagen: %d %s\n", $con_imagen, count( $sin_imagen ), $sin_imagen ? '(' . implode( ' / ', $sin_imagen ) . ')' : '' );

$ids = array_slice( $ids, 0, 3 );
foreach ( $ids as $id ) {
	$p = wc_get_product( $id );
	$cats = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'names' ) );
	printf(
		"- %s | sku=%s | \$%s | stock=%s | cats=%s | corta=%s\n",
		$p->get_name(),
		$p->get_sku(),
		$p->get_regular_price(),
		(string) $p->get_stock_quantity(),
		implode( ', ', is_wp_error( $cats ) ? array() : $cats ),
		mb_substr( wp_strip_all_tags( $p->get_short_description() ), 0, 60 )
	);
}

$marcas = array();
$tipos  = array();
foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) ) as $t ) {
	$marcas[] = $t->name . '(' . $t->count . ')';
}
printf( "Terminos con productos: %d -> %s\n", count( $marcas ), implode( ', ', $marcas ) );
