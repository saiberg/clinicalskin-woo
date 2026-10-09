<?php
/**
 * Diagnóstico de envíos: zonas, métodos y costos configurados en WooCommerce
 * (consulta directa a las tablas, sin cargar toda la clase de envíos).
 */
global $wpdb;

$zonas = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'woocommerce_shipping_zones ORDER BY zone_order, zone_id' );
echo 'zonas: ' . count( $zonas ) . PHP_EOL;
$zonas_ids = array( 0 );
foreach ( $zonas as $z ) {
	$zonas_ids[] = (int) $z->zone_id;
	$loc = $wpdb->get_results( $wpdb->prepare( 'SELECT location_type, location_code FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_locations WHERE zone_id = %d', $z->zone_id ) );
	$locs = array();
	foreach ( $loc as $l ) {
		$locs[] = $l->location_type . ':' . $l->location_code;
	}
	echo '- zona ' . $z->zone_id . ' "' . $z->zone_name . '" | ' . ( $locs ? implode( ', ', $locs ) : 'sin locaciones' ) . PHP_EOL;
}

echo 'métodos:' . PHP_EOL;
foreach ( $zonas_ids as $id ) {
	$metodos = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods WHERE zone_id = %d', $id ) );
	if ( ! $metodos ) {
		continue;
	}
	foreach ( $metodos as $m ) {
		$set = maybe_unserialize( $m->settings );
		echo '  - zona ' . $id . ' | ' . $m->method_id . ' | habilitado: ' . $m->is_enabled . ' | ' . json_encode( $set ) . PHP_EOL;
	}
}

echo 'país_base: ' . get_option( 'woocommerce_default_country' ) . PHP_EOL;
echo 'calcula_sin_direccion: ' . get_option( 'woocommerce_shipping_cost_requires_address' ) . PHP_EOL;
echo 'tiene_productos_con_envio: ' . get_option( 'woocommerce_shippable' ) . PHP_EOL;
echo 'no_enviar_a: ' . get_option( 'woocommerce_ship_to_countries' ) . PHP_EOL;
