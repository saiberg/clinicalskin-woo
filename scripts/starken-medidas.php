<?php
/**
 * Las unidades de la tienda son libras y pulgadas: los valores por defecto
 * (20/15/25) se interpretaban como pulgadas y salía una caja de 50 cm que
 * encarecía la tarifa. Deja un bulto pequeño de cosmética.
 */

$ajustes = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );

$ajustes['default_weight'] = '0.5';  /* lbs  */
$ajustes['default_width']  = '6';    /* in   */
$ajustes['default_height'] = '6';    /* in   */
$ajustes['default_length'] = '8';    /* in   */

update_option( 'woocommerce_wc_andresreyesdev_starken_settings', $ajustes );
update_option( 'woocommerce_wc_andresreyesdev_starken_5_settings', $ajustes );

foreach ( array( 'default_weight', 'default_width', 'default_height', 'default_length' ) as $k ) {
	echo $k . ' = ' . $ajustes[ $k ] . ' ' . get_option( 'woocommerce_weight_unit' ) . PHP_EOL;
}
