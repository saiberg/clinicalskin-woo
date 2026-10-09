<?php
/**
 * Habilita los métodos de pago del diseño y archiva las páginas basura.
 * Uso: wp eval-file /tmp/cs/habilitar-pagos.php --user=1
 */

/* ---------- 1. Métodos de pago ---------- */
$pagos = array(
	'woocommerce_bacs_settings' => array(
		'title'       => 'Transferencia bancaria',
		'description' => 'Transfiere a nuestra cuenta y envíanos el comprobante por WhatsApp. Preparamos tu pedido el mismo día.',
		'instructions' => 'Banco Estado · Cuenta Corriente 123-45678-90 · Clinical Skin SpA. Envía el comprobante a ventas@clinicalskin.cl indicando tu número de pedido.',
	),
	'woocommerce_cod_settings' => array(
		'title'       => 'Pago contra entrega',
		'description' => 'Pagas en efectivo al recibir tu pedido en tu domicilio. Disponible para toda Chile.',
	),
);

foreach ( $pagos as $clave => $datos ) {
	$ajustes = get_option( $clave, array() );
	if ( ! is_array( $ajustes ) ) {
		$ajustes = array();
	}
	$ajustes            = array_merge( $ajustes, $datos );
	$ajustes['enabled'] = 'yes';
	update_option( $clave, $ajustes );
	echo $clave . ' => ' . $ajustes['title'] . " (enabled=yes)\n";
}

/* El cheque no aplica en Chile: se queda deshabilitado. */
$cheque = get_option( 'woocommerce_cheque_settings', array() );
if ( ! is_array( $cheque ) ) {
	$cheque = array();
}
$cheque['enabled'] = 'no';
update_option( 'woocommerce_cheque_settings', $cheque );
echo "woocommerce_cheque_settings => enabled=no\n";

/* ---------- 2. Archivar páginas basura ---------- */
$privacidad = (int) get_option( 'wp_page_for_privacy_policy' );
$terminos   = (int) get_option( 'woocommerce_terms_page_id' );
echo 'privacidad=' . $privacidad . ' terminos=' . $terminos . "\n";

foreach ( array( 2, 3, 9 ) as $id ) {
	$publicacion = get_post( $id );
	if ( ! $publicacion ) {
		echo 'id ' . $id . " no existe\n";
		continue;
	}
	if ( $id === $privacidad || $id === $terminos ) {
		echo 'id ' . $id . ' (' . $publicacion->post_title . ") referenciada: NO se archiva\n";
		continue;
	}
	wp_update_post(
		array(
			'ID'          => $id,
			'post_status' => 'trash',
		)
	);
	echo 'id ' . $id . ' (' . $publicacion->post_title . ") => trash\n";
}
