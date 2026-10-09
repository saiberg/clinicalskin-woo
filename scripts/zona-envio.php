<?php
/**
 * Crea (o reutiliza) la zona de envío de Chile con tarifa plana de $3.990.
 * Uso: wp eval-file /tmp/cs/zona-envio.php --user=1
 */

if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
	echo "WooCommerce no cargado\n";
	return;
}

$zona  = null;
$zonas = WC_Shipping_Zones::get_zones();
echo 'zonas_existentes=' . count( $zonas ) . "\n";
if ( $zonas ) {
	echo 'estructura=' . wp_json_encode( reset( $zonas ) ) . "\n";
}

foreach ( $zonas as $existente ) {
	if ( is_object( $existente ) ) {
		$objetivo = $existente;
	} else {
		$id = 0;
		foreach ( array( 'zone_id', 'id', 'ID' ) as $clave ) {
			if ( isset( $existente[ $clave ] ) ) {
				$id = (int) $existente[ $clave ];
				break;
			}
		}
		if ( ! $id ) {
			continue;
		}
		$objetivo = new WC_Shipping_Zone( $id );
	}
	if ( 'Chile' === $objetivo->get_zone_name() ) {
		$zona = $objetivo;
		break;
	}
}

if ( ! $zona ) {
	$zona = new WC_Shipping_Zone();
	$zona->set_zone_name( 'Chile' );
	$zona->set_zone_order( 0 );
	$zona->save();
	echo "zona creada id=" . $zona->get_id() . "\n";
} else {
	echo "zona existente id=" . $zona->get_id() . "\n";
}

/* País: solo Chile. */
$tiene_cl = false;
foreach ( $zona->get_zone_locations() as $loc ) {
	if ( isset( $loc->code ) && 'CL' === $loc->code ) {
		$tiene_cl = true;
	}
}
if ( ! $tiene_cl ) {
	$zona->add_location( 'CL', 'country' );
	$zona->save();
	echo "ubicacion CL anadida\n";
}

/* Método: tarifa plana. */
$instancia = 0;
foreach ( $zona->get_shipping_methods() as $metodo ) {
	if ( 'flat_rate' === $metodo->id ) {
		$instancia = (int) $metodo->instance_id;
	}
}
if ( ! $instancia ) {
	$instancia = (int) $zona->add_shipping_method( 'flat_rate' );
	$zona->save();
	echo "metodo flat_rate anadido instance_id=" . $instancia . "\n";
}

update_option(
	'woocommerce_flat_rate_' . $instancia . '_settings',
	array(
		'title'        => 'Envío estándar',
		'tax_status'   => 'none',
		'cost'         => '3990',
		'type'         => 'class',
		'class_costs'  => array(),
		'class_labels' => array(),
	)
);

echo "settings guardados instance_id=" . $instancia . "\n";
echo "default_customer_address=" . get_option( 'woocommerce_default_customer_address' ) . "\n";
echo "default_country=" . get_option( 'woocommerce_default_country' ) . "\n";
echo "zonas_totales=" . count( WC_Shipping_Zones::get_zones() ) . "\n";
