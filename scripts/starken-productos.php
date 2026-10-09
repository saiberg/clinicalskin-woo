<?php
/**
 * Datos físicos del producto del carrito de prueba y unidades de la tienda
 * para entender de dónde sale la tarifa de Starken.
 */

echo 'unidades: peso=' . get_option( 'woocommerce_weight_unit' ) . ' medida=' . get_option( 'woocommerce_dimension_unit' ) . PHP_EOL;

foreach ( array( 70, 71, 72, 73 ) as $id ) {
	$p = wc_get_product( $id );
	if ( ! $p ) {
		continue;
	}
	echo 'producto ' . $id . ' (' . $p->get_name() . '): peso=' . var_export( $p->get_weight(), true )
		. ' ancho=' . var_export( $p->get_width(), true )
		. ' alto=' . var_export( $p->get_height(), true )
		. ' largo=' . var_export( $p->get_length(), true ) . PHP_EOL;
}

$ajustes = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );
foreach ( array( 'origin', 'enable_default', 'default_weight', 'default_width', 'default_height', 'default_length', 'profit_type', 'profit_amount', 'hide_express', 'hide_agency' ) as $k ) {
	echo '  ' . $k . ' = ' . ( $ajustes[ $k ] ?? '(sin clave)' ) . PHP_EOL;
}
