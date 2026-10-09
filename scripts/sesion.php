<?php
/**
 * Utilidad de mantenimiento: vacía las sesiones de WooCommerce (carrito y
 * método de pago elegido) y muestra el orden de los gateways.
 */
global $wpdb;
$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}woocommerce_sessions" );
echo 'sesiones: ' . $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_sessions" ) . PHP_EOL;
echo 'orden: ' . json_encode( get_option( 'woocommerce_gateway_order' ) ) . PHP_EOL;
echo 'gateways: ' . implode( ',', array_keys( WC()->payment_gateways()->payment_gateways ) ) . PHP_EOL;
