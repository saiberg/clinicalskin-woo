<?php
/**
 * Añade la instancia de Starken (método del plugin) a la zona Chile, con las
 * mismas opciones globales ya guardadas, el título y el subtítulo del diseño,
 * y desactiva el Starken fijo ($3.990) que tenía el tema.
 */

$zona = new WC_Shipping_Zone( 1 );

$existe = null;
foreach ( $zona->get_shipping_methods( false ) as $m ) {
	if ( 'wc_andresreyesdev_starken' === $m->id ) {
		$existe = (int) $m->get_instance_id();
	}
}

if ( ! $existe ) {
	$existe = (int) $zona->add_shipping_method( 'wc_andresreyesdev_starken' );
}

$global = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );
$instancia = array_merge(
	$global,
	array(
		'enabled'     => 'yes',
		'title'       => 'Starken a domicilio',
		'descripcion' => '2 a 3 días hábiles',
	)
);
update_option( 'woocommerce_wc_andresreyesdev_starken_' . $existe . '_settings', $instancia );

/* El Starken fijo del tema deja de existir: su tarjeta es la del plugin. */
$flat = (array) get_option( 'woocommerce_flat_rate_2_settings', array() );
$flat['enabled'] = 'no';
update_option( 'woocommerce_flat_rate_2_settings', $flat );

/* El método también se enciende desde la configuración global. */
$global['enabled'] = 'yes';
update_option( 'woocommerce_wc_andresreyesdev_starken_settings', $global );

$guardado = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_' . $existe . '_settings', array() );
echo 'instancia: ' . $existe . PHP_EOL;
echo 'opciones_instancia: ' . implode( ', ', array_keys( $guardado ) ) . PHP_EOL;
echo 'log: ' . ( file_exists( WC_LOG_DIR . 'starken-pro' ) || glob( WC_LOG_DIR . 'starken-pro*' ) ? 'si' : 'no' ) . PHP_EOL;
