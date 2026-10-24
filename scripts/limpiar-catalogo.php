<?php
/* Vacia el catalogo completo (productos, adjuntos y categorias) para recargar desde cero. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ids = get_posts(
	array(
		'post_type'      => 'product',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'trash' ),
		'numberposts'    => -1,
		'fields'         => 'ids',
		'suppress_filters' => false,
	)
);

$adjuntos = array();
foreach ( $ids as $id ) {
	$todos = array( (int) get_post_thumbnail_id( $id ) );
	$meta  = get_post_meta( $id, '_product_image_gallery', true );
	if ( is_string( $meta ) && '' !== $meta ) {
		foreach ( explode( ',', $meta ) as $g ) {
			$todos[] = (int) $g;
		}
	}
	foreach ( $todos as $a ) {
		if ( $a > 0 ) {
			$adjuntos[] = $a;
		}
	}
	wp_delete_post( (int) $id, true ); // borrado definitivo
}

foreach ( array_unique( $adjuntos ) as $a ) {
	wp_delete_attachment( $a, true );
}

$terms = 0;
foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) ) as $t ) {
	if ( wp_delete_term( (int) $t->term_id, 'product_cat' ) ) {
		$terms++;
	}
}

printf(
	"Productos borrados: %d | adjuntos borrados: %d | categorias borradas: %d\n",
	count( $ids ),
	count( array_unique( $adjuntos ) ),
	$terms
);
