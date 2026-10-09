<?php
/**
 * El origen que espera la API de Starken es el código de comuna de la lista
 * del plugin (1 => SANTIAGO), no el nombre: corrige el valor guardado.
 */

$ajustes = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );
$antes   = $ajustes['origin'] ?? '(sin valor)';

$ajustes['origin'] = '1';
update_option( 'woocommerce_wc_andresreyesdev_starken_settings', $ajustes );

/* La instancia 5 del plugin lee su opcion global, pero por si acaso. */
$instancia = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_5_settings', array() );
if ( $instancia ) {
	$instancia['origin'] = '1';
	update_option( 'woocommerce_wc_andresreyesdev_starken_5_settings', $instancia );
}

echo 'origen: ' . $antes . ' -> ' . $ajustes['origin'] . PHP_EOL;

$lista = (array) get_transient( 'wc_starken_origin' );
echo 'codigo_valido: ' . ( isset( $lista['1'] ) ? 'si (' . $lista['1'] . ')' : 'no' ) . PHP_EOL;
