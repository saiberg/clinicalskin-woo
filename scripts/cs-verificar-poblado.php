<?php
/**
 * Verifica asignaciones de atributos y metadatos por producto (Fase 1).
 * Uso: wp eval-file /tmp/cs/cs-verificar-poblado.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ids = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => array( 'publish' ) ) );
$ok = 0;
$sin = 0;
foreach ( $ids as $id ) {
	$p    = wc_get_product( $id );
	$atts = $p->get_attributes();
	$partes = array();
	foreach ( $atts as $tax => $attr ) {
		if ( $attr->is_taxonomy() ) {
			$nombres = array();
			foreach ( $attr->get_terms() as $term ) {
				$nombres[] = $term->name;
			}
			$partes[] = $tax . '=[' . implode( ', ', $nombres ) . ']';
		}
	}
	$porque = get_post_meta( $id, 'cs_porque', true );
	if ( $partes ) {
		++$ok;
		echo $p->get_sku() . "\n";
		echo '    ' . implode( ' | ', $partes ) . "\n";
		echo '    cs_porque: ' . ( $porque ? 'sí' : 'NO' ) . "\n";
	} else {
		++$sin;
		echo 'SIN ATRIBUTOS: ' . $p->get_sku() . "\n";
	}
}
echo "\nCon atributos: {$ok} | Sin atributos: {$sin}\n";
