<?php
$estados = WC()->countries->get_states( 'CL' );
echo 'estados CL: ' . json_encode( $estados ) . PHP_EOL;
$o = wc_get_order( 97 );
if ( $o ) {
	echo 'pedido97 state: [' . $o->get_billing_state() . '] city: [' . $o->get_billing_city() . ']' . PHP_EOL;
}
$locales = WC()->countries->get_country_locale();
if ( isset( $locales['CL'] ) ) {
	echo 'locale CL: ' . json_encode( $locales['CL'] ) . PHP_EOL;
}
