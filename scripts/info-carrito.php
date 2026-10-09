<?php
/**
 * Diagnóstico: zonas de envío (tabla directa) + métodos + totales.
 * wp eval-file /tmp/cs/info-carrito.php --user=1
 */
global $wpdb;

$zonas = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zones" );
echo 'zonas: ' . count( $zonas ) . "\n";
foreach ( $zonas as $z ) {
	$metodos = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE zone_id = %d", $z->zone_id ) );
	echo ' - #' . $z->zone_id . ' ' . $z->zone_name . ' (order ' . $z->zone_order . ")\n";
	foreach ( $metodos as $m ) {
		echo '    ' . $m->method_id . ' enabled=' . $m->enabled . "\n";
	}
}

$opciones = get_option( 'woocommerce_shipping_cost_requires_address', '' );
echo 'costo_requiere_dir: ' . var_export( $opciones, true ) . "\n";
echo 'ship_to_countries: ' . get_option( 'woocommerce_ship_to_countries', '' ) . "\n";
echo 'zonas_defecto: ' . get_option( 'woocommerce_default_customer_address', '' ) . "\n";

echo 'needs_shipping: ' . ( WC()->cart->needs_shipping() ? 'si' : 'no' ) . "\n";
echo 'show_shipping: ' . ( WC()->cart->show_shipping() ? 'si' : 'no' ) . "\n";
echo 'count: ' . WC()->cart->get_cart_contents_count() . "\n";
echo 'subtotal_html: ' . WC()->cart->get_cart_subtotal() . "\n";
echo 'subtotal_raw: ' . WC()->cart->get_subtotal() . "\n";
echo 'discount_raw: ' . WC()->cart->get_discount_total() . "\n";
echo 'shipping_raw: ' . WC()->cart->get_shipping_total() . "\n";
echo 'total_raw: ' . WC()->cart->get_total( 'edit' ) . "\n";
echo 'total_html: ' . wc_price( WC()->cart->get_total( 'edit' ) ) . "\n";
echo 'impuestos: ' . WC()->cart->get_total_tax() . "\n";
echo 'moneda: ' . get_woocommerce_currency() . ' ' . get_woocommerce_currency_symbol() . "\n";
echo 'paypal/bacs/cod gateways: ' . implode( ', ', array_keys( WC()->payment_gateways()->payment_gateways() ) ) . "\n";
foreach ( WC()->payment_gateways()->payment_gateways() as $id => $g ) {
	echo '   ' . $id . ' enabled=' . ( $g->enabled ?? '?' ) . "\n";
}
