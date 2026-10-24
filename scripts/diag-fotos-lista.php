<?php
/* Imprime: producto_id | sku | archivo_original de la foto (para copiarla en el host). */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$elegidos = array( 206, 262, 200, 254, 256, 216, 186, 222, 212, 268, 192 );
foreach ( $elegidos as $id ) {
	$thumb = (int) get_post_thumbnail_id( $id );
	$ruta  = $thumb ? get_attached_file( $thumb ) : '';
	printf( "%d | %s | %s\n", $id, get_post_meta( $id, '_sku', true ), $ruta ? basename( $ruta ) : 'SIN-FOTO' );
}
