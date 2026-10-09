<?php
/**
 * Borra terminos vacios que hayan quedado en los atributos globales
 * (restos de importaciones anteriores).
 *
 * Uso: wp eval-file /tmp/cs/limpiar-terminos.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$borrados = 0;
foreach ( wc_get_attribute_taxonomies() as $attr ) {
	$tax = wc_attribute_taxonomy_name( $attr->attribute_name );
	foreach ( get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) ) as $term ) {
		if ( $term->count > 0 ) {
			continue;
		}
		wp_delete_term( $term->term_id, $tax );
		echo "Termino borrado ({$tax}): {$term->name}\n";
		++$borrados;
	}
}
echo "Total: {$borrados}\n";
