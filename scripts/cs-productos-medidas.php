<?php
$ids = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => 'any' ) );
foreach ( $ids as $id ) {
	$p = wc_get_product( $id );
	echo $id . ' | ' . $p->get_name() . ' | w=' . var_export( $p->get_width(), true ) .
		' h=' . var_export( $p->get_height(), true ) .
		' l=' . var_export( $p->get_length(), true ) .
		' peso=' . var_export( $p->get_weight(), true ) .
		' dims=' . ( $p->has_dimensions() ? 'si' : 'no' ) . PHP_EOL;
}
echo 'unidad dimensiones: ' . get_option( 'woocommerce_dimension_unit' ) . ' | unidad peso: ' . get_option( 'woocommerce_weight_unit' ) . PHP_EOL;
