<?php
global $wpdb;
$rows = $wpdb->get_results( "SELECT zone_id, instance_id, method_id, is_enabled FROM {$wpdb->prefix}woocommerce_shipping_zone_methods ORDER BY zone_id, instance_id" );
foreach ( $rows as $r ) {
	echo 'zona ' . $r->zone_id . ' | inst ' . $r->instance_id . ' | ' . $r->method_id . ' | ' . ( $r->is_enabled ? 'si' : 'no' ) . PHP_EOL;
}
echo 'settings instancia 6: ' . json_encode( get_option( 'woocommerce_chilexpress_woo_oficial_6_settings' ) ) . PHP_EOL;
echo 'settings global cxp: ' . json_encode( get_option( 'woocommerce_chilexpress_woo_oficial_settings' ) ) . PHP_EOL;
