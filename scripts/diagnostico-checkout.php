<?php
/** Diagnóstico rápido de los campos y del bloque de dirección de envío. */
$checkout = WC()->checkout();
$billing  = $checkout->get_checkout_fields( 'billing' );

foreach ( array( 'billing_email', 'billing_phone', 'billing_city', 'billing_address_2', 'billing_country', 'billing_state', 'billing_address_1' ) as $clave ) {
	if ( isset( $billing[ $clave ] ) ) {
		echo $clave . ' | label=' . $billing[ $clave ]['label'] . ' | placeholder=' . ( $billing[ $clave ]['placeholder'] ?? '-' ) . ' | class=' . implode( ',', (array) $billing[ $clave ]['class'] ) . "\n";
	} else {
		echo $clave . " | NO EXISTE\n";
	}
}

$orden = $checkout->get_checkout_fields( 'order' );
echo 'order_comments=' . ( isset( $orden['order_comments'] ) ? $orden['order_comments']['label'] : 'NO' ) . "\n";

echo 'needs_shipping_address=' . var_export( WC()->cart->needs_shipping_address(), true ) . "\n";
echo 'needs_shipping=' . var_export( WC()->cart->needs_shipping(), true ) . "\n";
echo 'show_shipping=' . var_export( WC()->cart->show_shipping(), true ) . "\n";
echo 'ship_to_destination=' . get_option( 'woocommerce_ship_to_destination' ) . "\n";
echo 'ship_to_billing_address_only=' . var_export( function_exists( 'wc_ship_to_billing_address_only' ) ? wc_ship_to_billing_address_only() : null, true ) . "\n";
echo 'order_button_text=' . apply_filters( 'woocommerce_checkout_order_button_text', 'x' ) . "\n";
echo 'envio_elegido=' . WC()->cart->get_chosen_shipping_method() . "\n";
echo 'total=' . WC()->cart->get_total( 'edit' ) . "\n";
