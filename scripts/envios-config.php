<?php
/**
 * Métodos de envío del diseño: Starken $3.990, BlueExpress $4.990 y
 * retiro en Santiago gratis (zona "Chile").
 */
if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
	echo 'sin WooCommerce' . PHP_EOL;
	return;
}

$zona = new WC_Shipping_Zone( 1 );
if ( ! $zona || ! $zona->get_id() ) {
	echo 'zona 1 no existe' . PHP_EOL;
	return;
}

/* Limpia lo que hubiera (p. ej. el "Envío estándar" genérico). */
foreach ( $zona->get_shipping_methods( false ) as $existente ) {
	$zona->delete_shipping_method( $existente->get_instance_id() );
	echo 'eliminado ' . $existente->id . ' ' . $existente->get_title() . PHP_EOL;
}

$plan = array(
	array( 'flat_rate', 'Starken a domicilio', '2 a 3 días hábiles', '3990' ),
	array( 'flat_rate', 'BlueExpress express', '1 a 2 días hábiles', '4990' ),
	array( 'local_pickup', 'Retiro en punto de atención', 'Santiago · listo en 24 horas', '0' ),
);

foreach ( $plan as $fila ) {
	list( $tipo, $titulo, $descripcion, $costo ) = $fila;

	$clase = 'WC_Shipping_' . str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $tipo ) ) );
	if ( ! class_exists( $clase ) ) {
		echo 'sin clase ' . $clase . PHP_EOL;
		continue;
	}

	/* add_shipping_method() espera el tipo ("flat_rate"), no el objeto. */
	$instancia = $zona->add_shipping_method( $tipo );
	if ( ! $instancia ) {
		echo 'fallo al dar de alta ' . $tipo . PHP_EOL;
		continue;
	}

	/* WC_Shipping_Method no tiene set_instance_option(): se escribe el option. */
	$metodo          = new $clase( $instancia );
	$metodo->enabled = 'yes';
	$metodo->init_instance_settings();
	$metodo->instance_settings['title']       = $titulo;
	$metodo->instance_settings['descripcion'] = $descripcion;
	$metodo->instance_settings['cost']        = $costo;
	$metodo->instance_settings['tax_status']  = 'none';
	update_option( $metodo->get_instance_option_key(), $metodo->instance_settings, 'yes' );

	echo 'alta ' . $tipo . ':' . $instancia . ' | ' . $titulo . ' | ' . $costo . PHP_EOL;
}

/* Sin "resto del mundo": la tienda solo envía a Chile. */
$resto = new WC_Shipping_Zone( 0 );
foreach ( $resto->get_shipping_methods( false ) as $existente ) {
	$resto->delete_shipping_method( $existente->get_instance_id() );
	echo 'zona 0: eliminado ' . $existente->id . PHP_EOL;
}

echo 'listo' . PHP_EOL;
