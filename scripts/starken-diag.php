<?php
/**
 * Diagnóstico: qué opción lee cada método de la zona 1 y por qué el Starken
 * fijo seguía activo a pesar de tener enabled=no.
 */

echo '--- zona 1 en base ---' . PHP_EOL;
global $wpdb;
foreach ( $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods WHERE zone_id = 1' ) as $f ) {
	echo '  zone_method_id=' . $f->zone_method_id . ' | instance_id=' . $f->instance_id . ' | method_id=' . $f->method_id . PHP_EOL;
}

echo '--- opcion_leida ---' . PHP_EOL;
$zona = new WC_Shipping_Zone( 1 );
foreach ( $zona->get_shipping_methods( false ) as $m ) {
	echo '  ' . $m->id . ' ' . $m->get_instance_id() . ' -> ' . $m->get_option_key()
		. " | enabled='" . $m->get_option( 'enabled' ) . "'"
		. " | title='" . $m->get_title() . "'"
		. ' | is_enabled=' . ( $m->is_enabled() ? 'si' : 'no' ) . PHP_EOL;
}

$opciones = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'woocommerce_flat_rate%' OR option_name LIKE 'woocommerce_local_pickup%' OR option_name LIKE 'woocommerce_wc_andresreyesdev%'" );
echo '  (nombres de opciones en base: ' . implode( ', ', $opciones ) . ')' . PHP_EOL;
echo '  (opciones plan: wc_starken_plan=' . var_export( get_transient( 'wc_starken_plan' ), true ) . ')' . PHP_EOL;
