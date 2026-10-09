<?php
/**
 * Diagnóstico definitivo de la zona 1: opción que lee cada método y si está
 * habilitado, con objetos creados al vuelo y con los que devuelve la zona.
 * (La instancia fija de Starken quedó en enabled=no.)
 */

echo '--- flat_rate instance 2 (objeto nuevo) ---' . PHP_EOL;
$m = new WC_Shipping_Flat_Rate( 2 );
echo '  option_key=' . $m->get_option_key()
	. " | enabled='" . $m->get_option( 'enabled' ) . "'"
	. " | prop='" . $m->enabled . "'"
	. ' | is_enabled=' . ( $m->is_enabled() ? 'si' : 'no' )
	. " | title='" . $m->get_title() . "'" . PHP_EOL;

echo '--- objeto de la zona ---' . PHP_EOL;
$zona = new WC_Shipping_Zone( 1 );
foreach ( $zona->get_shipping_methods( false ) as $x ) {
	echo '  ' . $x->id . ' ' . $x->get_instance_id() . ' -> ' . $x->get_option_key()
		. " | enabled='" . $x->get_option( 'enabled' ) . "'"
		. " | prop='" . $x->enabled . "'"
		. ' | is_enabled=' . ( $x->is_enabled() ? 'si' : 'no' )
		. ' | ' . $x->get_title() . PHP_EOL;
}
