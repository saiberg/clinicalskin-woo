<?php
/**
 * Cupón "rutina": 10% de descuento cuando el carrito tiene 2 o más productos.
 *
 * El tema lo aplica y lo quita solo (ver "Descuento de la rutina" en
 * functions.php); esta plantilla solo lo crea si todavía no existe.
 */

$existe = wc_get_coupon_id_by_code( 'rutina' );
if ( $existe ) {
	echo "ya existe (id {$existe})\n";
	return;
}

$cupon = new WC_Coupon();
$cupon->set_code( 'rutina' );
$cupon->set_discount_type( 'percent' );
$cupon->set_amount( 10 );
$cupon->set_description( 'Pack rutina completa (-10%)' );
$cupon->set_status( 'publish' );
$cupon->save();

echo 'creado id ' . $cupon->get_id() . "\n";
