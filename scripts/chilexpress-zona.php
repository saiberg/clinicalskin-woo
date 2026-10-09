<?php
/**
 * Chilexpress: alias de región (formato legacy CL-XX) + método en la zona Chile.
 */

/* 1) Alias de regiones legacy (checkout viejo / caché de JS) */
$alias = array(
	'CL-TA' => 'R1',  // Tarapacá
	'CL-AN' => 'R2',  // Antofagasta
	'CL-AT' => 'R3',  // Atacama
	'CL-CO' => 'R4',  // Coquimbo
	'CL-VS' => 'R5',  // Valparaíso
	'CL-LI' => 'R6',  // O'Higgins
	'CL-ML' => 'R7',  // Maule
	'CL-BI' => 'R8',  // Biobío
	'CL-AR' => 'R9',  // Araucanía
	'CL-LL' => 'R10', // Los Lagos
	'CL-AI' => 'R11', // Aysén
	'CL-MA' => 'R12', // Magallanes
	'CL-LR' => 'R14', // Los Ríos
	'CL-AP' => 'R15', // Arica y Parinacota
	'CL-NB' => 'R16', // Ñuble
	'CL-RM' => 'RM',  // Metropolitana
);
$rc = (array) get_option( 'chilexpress_woo_oficial_region_comuna', array() );
$regiones = isset( $rc['regiones_habilitadas'] ) && is_array( $rc['regiones_habilitadas'] )
	? $rc['regiones_habilitadas'] : array();
$antes = count( $regiones );
$regiones = array_values( array_unique( array_merge( $regiones, array_keys( $alias ) ) ) );
$rc['regiones_habilitadas'] = $regiones;
update_option( 'chilexpress_woo_oficial_region_comuna', $rc );
echo 'regiones habilitadas: ' . $antes . ' -> ' . count( $regiones ) . PHP_EOL;
echo 'comunas habilitadas: ' . count( (array) ( $rc['comunas_habilitadas'] ?? array() ) ) . PHP_EOL;

/* 2) Método en la zona 1 (Chile) */
$zona       = new WC_Shipping_Zone( 1 );
$instancia  = 0;
$existe     = false;
foreach ( $zona->get_shipping_methods() as $metodo ) {
	if ( 'chilexpress_woo_oficial' === $metodo->id ) {
		$existe     = true;
		$instancia  = $metodo->get_instance_id();
	}
}
if ( ! $existe ) {
	$nuevo = $zona->add_shipping_method( 'chilexpress_woo_oficial' );
	if ( is_wp_error( $nuevo ) ) {
		echo 'FALTA alta: ' . $nuevo->get_error_message() . PHP_EOL;
		return;
	}
	$instancia = (int) $nuevo;
}
echo 'instancia: ' . $instancia . PHP_EOL;

$global = (array) get_option( 'woocommerce_chilexpress_woo_oficial_settings', array() );
$global['enabled'] = 'yes';
$global['title']   = 'Chilexpress';
update_option( 'woocommerce_chilexpress_woo_oficial_settings', $global );

$local = (array) get_option( 'woocommerce_chilexpress_woo_oficial_' . $instancia . '_settings', array() );
$local['enabled'] = 'yes';
$local['title']   = 'Chilexpress';
update_option( 'woocommerce_chilexpress_woo_oficial_' . $instancia . '_settings', $local );
echo 'método habilitado en la zona 1.' . PHP_EOL;

/* 3) Tarjetas de la zona */
foreach ( $zona->get_shipping_methods() as $metodo ) {
	echo '  - ' . $metodo->id . ' #' . $metodo->get_instance_id() . ' ' . ( $metodo->enabled ?? '?' ) . PHP_EOL;
}
