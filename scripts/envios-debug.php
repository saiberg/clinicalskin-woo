<?php
/** Diagnóstico de la tabla de métodos de envío tras la configuración. */
global $wpdb;
$tabla = $wpdb->prefix . 'woocommerce_shipping_zone_methods';
$filas = $wpdb->get_results( 'SELECT * FROM ' . $tabla );
echo 'filas: ' . count( $filas ) . ' | ultimo_error: ' . $wpdb->last_error . PHP_EOL;
foreach ( $filas as $f ) {
	$set = get_option( 'woocommerce_' . $f->method_id . '_' . $f->instance_id . '_settings', null );
	echo 'zona ' . $f->zone_id . ' | ' . $f->method_id . ' | instancia ' . $f->instance_id . ' | orden ' . $f->zone_order . ' | habilitado ' . $f->is_enabled . ' | ' . json_encode( $set ) . PHP_EOL;
}
$zonas = $wpdb->get_results( 'SELECT zone_id, zone_name FROM ' . $wpdb->prefix . 'woocommerce_shipping_zones' );
foreach ( $zonas as $z ) {
	echo 'zona: ' . $z->zone_id . ' ' . $z->zone_name . PHP_EOL;
}
