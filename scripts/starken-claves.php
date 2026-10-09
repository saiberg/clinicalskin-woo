<?php
/**
 * Lee el plugin de Starken y enumera: la constante de su API, las claves de
 * ajuste que usa y los nombres de campo que expone en el panel.
 */

$fichero = WP_PLUGIN_DIR . '/calculo-de-despacho-via-starken-para-woocommerce/calculo-de-despacho-via-starken-para-woocommerce.php';
$src     = file_get_contents( $fichero );

preg_match( "/define\(\s*'(ARG_STARKEN_PLUGIN_API_BASE_URL)'\s*,\s*'([^']+)'/", $src, $m );
echo 'api_base: ' . ( isset( $m[2] ) ? $m[2] : '(no encontrada)' ) . PHP_EOL;

/* Claves leídas/guardadas en los ajustes del método. */
preg_match_all( "/woocommerce_wc_andresreyesdev_starken_settings'\s*,\s*\[[^\]]*\]\s*\)|starken\['([a-z0-9_]+)'\]|settings\['([a-z0-9_]+)'\]/", $src, $m2 );
$claves = array_unique( array_filter( array_merge( $m2[1], $m2[2] ) ) );
echo 'claves_de_ajuste: ' . implode( ', ', $claves ) . PHP_EOL;

/* Nombres de campo del formulario de administración. */
preg_match_all( "/woocommerce_wc_andresreyesdev_starken_settings\[([a-z0-9_]+)\]/", $src, $m3 );
echo 'campos_formulario: ' . implode( ', ', array_unique( $m3[1] ) ) . PHP_EOL;

/* Etiquetas visibles de los campos (para saber qué va en cada uno). */
preg_match_all( "/woocommerce_wc_andresreyesdev_starken_settings\[([a-z0-9_]+)\][^>]*>(?:.|\n){0,400}?<label[^>]*>([^<]{3,70})</", $src, $m4 );
$pares = array();
foreach ( $m4[1] as $i => $clave ) {
	$pares[ $clave ] = trim( preg_replace( '/\s+/', ' ', $m4[2][ $i ] ) );
}
foreach ( $pares as $clave => $etiqueta ) {
	echo '  ' . $clave . ' -> ' . $etiqueta . PHP_EOL;
}
