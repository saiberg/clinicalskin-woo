<?php
/**
 * Da de alta la tienda en el portal de Anyda (genera la API Key gratis que
 * usa el plugin de Starken) y guarda el resto de ajustes del método:
 * origen, medidas por defecto, sin agencias ni express (para no alterar el
 * diseño) y costos de respaldo si la API no contesta.
 */

$ajustes = get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );

/* Solo registra si todavía no hay clave. */
if ( empty( $ajustes['api'] ) ) {
	$respuesta = wp_remote_post(
		'https://www.anyda.xyz/api/starken/register',
		array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body'        => wp_json_encode(
				array(
					'domain' => site_url(),
					'email'  => get_option( 'admin_email' ),
				)
			),
			'data_format' => 'body',
			'timeout'     => 20,
		)
	);

	echo '--- registro ---' . PHP_EOL;
	if ( is_wp_error( $respuesta ) ) {
		echo 'error: ' . $respuesta->get_error_message() . PHP_EOL;
		return;
	}
	$codigo = (int) wp_remote_retrieve_response_code( $respuesta );
	$cuerpo = json_decode( wp_remote_retrieve_body( $respuesta ), true );
	echo 'codigo: ' . $codigo . PHP_EOL;
	echo 'plan: ' . ( $cuerpo['plan'] ?? '(ninguno)' ) . PHP_EOL;
	echo 'token: ' . ( $cuerpo['token'] ?? ( $cuerpo['error'] ?? '(ninguno)' ) ) . PHP_EOL;
	echo 'correo: ' . ( $cuerpo['email'] ?? '' ) . PHP_EOL;

	if ( empty( $cuerpo['token'] ) ) {
		return;
	}
	$ajustes['api']  = $cuerpo['token'];
	$ajustes['plan'] = $cuerpo['plan'];
	update_option( 'woocommerce_wc_andresreyesdev_starken_settings', $ajustes );
	set_transient( 'wc_starken_plan', $cuerpo['plan'], 30 * MINUTE_IN_SECONDS );
}

/* Ajustes del método (lo que se ve en el checkout se mantiene igual). */
$ajustes = array_merge(
	(array) get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() ),
	array(
		'enabled'                     => 'no', /* se enciende cuando la instancia exista */
		'title'                       => 'Starken a domicilio',
		'origin'                      => 'Santiago',
		'hide_agency'                 => 'yes',
		'hide_express'                => 'yes',
		'enable_default'              => 'yes',
		'default_weight'              => '0.5',
		'default_width'               => '20',
		'default_height'              => '15',
		'default_length'              => '25',
		'enable_fallback'             => 'yes',
		'fallback_cost'               => '3990',
		'profit_type'                 => 'none',
		'profit_amount'               => '0',
		'enable_logging'              => 'yes',
		'show_logo_checkout'          => 'no',
		'enable_tracking_notifications' => 'no',
	)
);
update_option( 'woocommerce_wc_andresreyesdev_starken_settings', $ajustes );

echo '--- ajustes ---' . PHP_EOL;
echo 'api: ' . substr( (string) $ajustes['api'], 0, 16 ) . '…' . PHP_EOL;
echo 'origen: ' . $ajustes['origin'] . ' | respaldo: ' . $ajustes['fallback_cost'] . PHP_EOL;
echo 'verificado: ' . get_option( 'wc_starken_connection_verified', 'no' ) . PHP_EOL;
echo 'plan: ' . ( function_exists( 'arg_starken_refresh_plan' ) ? arg_starken_refresh_plan() : '(n/a)' ) . PHP_EOL;
echo 'habilitado: ' . $ajustes['enabled'] . PHP_EOL;

/* Estado de la zona 1. */
echo '--- instancias de la zona 1 ---' . PHP_EOL;
$zona = new WC_Shipping_Zone( 1 );
foreach ( $zona->get_shipping_methods( false ) as $m ) {
	echo '  ' . $m->get_instance_id() . ' ' . $m->id . ' | habilitado=' . ( $m->is_enabled() ? 'si' : 'no' ) . ' | ' . $m->get_title() . PHP_EOL;
}
