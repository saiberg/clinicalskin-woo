<?php
/**
 * 1) Borra de la zona Chile el Starken fijo (flat_rate instance 2): su tarjeta
 *    ahora es la del plugin, que cotiza en vivo.
 * 2) Deja el texto secundario ("2 a 3 días hábiles") también en la opción
 *    global de Starken, porque el método del plugin no usa instancias.
 */

global $wpdb;

$borradas = $wpdb->query(
	'DELETE FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods WHERE zone_id = 1 AND instance_id = 2'
);

echo 'filas borradas: ' . var_export( $borradas, true ) . PHP_EOL;

$global = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );
$global['descripcion'] = '2 a 3 días hábiles';
$global['enabled']     = 'yes';
update_option( 'woocommerce_wc_andresreyesdev_starken_settings', $global );

echo 'descripcion global: ' . $global['descripcion'] . PHP_EOL;
echo '--- zona 1 ---' . PHP_EOL;
$zona = new WC_Shipping_Zone( 1 );
foreach ( $zona->get_shipping_methods( false ) as $m ) {
	echo '  ' . $m->get_instance_id() . ' ' . $m->id . ' | is_enabled=' . ( $m->is_enabled() ? 'si' : 'no' ) . ' | ' . $m->get_title() . PHP_EOL;
}
