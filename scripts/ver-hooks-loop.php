<?php
/**
 * Lista los callbacks enganchados a los hooks del loop de productos
 * (para quitar el precio y el botón de añadir del listado de la tienda).
 * Uso: wp eval-file /tmp/cs/ver-hooks-loop.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$buscados = array(
	'woocommerce_before_shop_loop_item_title',
	'woocommerce_after_shop_loop_item_title',
	'woocommerce_after_shop_loop_item',
	'woocommerce_before_shop_loop_item',
	'woocommerce_shop_loop_item_title',
);

$archivos = array_merge(
	glob( WP_PLUGIN_DIR . '/woocommerce/includes/*.php' ),
	glob( WP_PLUGIN_DIR . '/woocommerce/includes/**/*.php' )
);

foreach ( $archivos as $archivo ) {
	foreach ( file( $archivo ) as $n => $linea ) {
		if ( ! preg_match( '/add_action\(\s*[\'"]([^\'"]+)[\'"]/', $linea, $m ) ) {
			continue;
		}
		if ( ! in_array( $m[1], $buscados, true ) ) {
			continue;
		}
		echo basename( $archivo ) . ':' . ( $n + 1 ) . ' ' . trim( $linea ) . "\n";
	}
}
