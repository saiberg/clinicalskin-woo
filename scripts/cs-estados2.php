<?php
$filtrado = apply_filters( 'woocommerce_states', WC()->countries->get_states( 'CL' ), 'CL' );
echo 'estados filtrados: ' . json_encode( array_keys( (array) $filtrado ) ) . PHP_EOL;

ob_start();
woocommerce_form_field( 'billing_state', array(
	'type'    => 'state',
	'label'   => 'Región',
	'required' => true,
	'class'   => array( 'form-row', 'field' ),
	'options' => $filtrado,
), '' );
$html = ob_get_clean();
preg_match_all( '/<option[^>]*value="([^"]*)"/', $html, $m );
echo 'opciones del select: ' . json_encode( $m[1] ) . PHP_EOL;
