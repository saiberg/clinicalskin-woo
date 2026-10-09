<?php
$control = WC()->payment_gateways();
$control->init();
foreach ( $control->payment_gateways() as $id => $gateway ) {
	echo $id . ' | enabled=' . $gateway->enabled . ' | ' . $gateway->title . PHP_EOL;
}
$disponibles = $control->get_available_payment_gateways();
echo 'disponibles: ' . ( $disponibles ? implode( ', ', array_keys( $disponibles ) ) : '(ninguno)' ) . PHP_EOL;
