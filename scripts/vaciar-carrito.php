<?php
/** Vacía el carrito para dejar la tienda en estado limpio tras las pruebas. */
if ( function_exists( 'WC' ) && WC()->cart ) {
	WC()->cart->empty_cart();
	echo 'carrito vaciado' . PHP_EOL;
}
