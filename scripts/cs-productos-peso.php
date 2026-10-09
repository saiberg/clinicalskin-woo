<?php
/**
 * Peso por defecto en los productos: sin peso, Starken cae en fatal
 * (TypeError) cuando Chilexpress marca dimensiones en memoria.
 */
$ids = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => 'any' ) );
$n   = 0;
foreach ( $ids as $id ) {
	$p = wc_get_product( $id );
	if ( '' === (string) $p->get_weight() ) {
		$p->set_weight( '0.5' );
		$p->save();
		$n++;
		echo 'peso 0.5 -> ' . $id . ' ' . $p->get_name() . PHP_EOL;
	}
}
echo 'productos ajustados: ' . $n . PHP_EOL;
