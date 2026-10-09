<?php
/**
 * Deja el catalogo limpio para reimportar:
 *  - borra productos (se reimportan desde el CSV)
 *  - deja UN atributo global por nombre esperado y borra los duplicados/chapuceros
 *
 * Uso: wp eval-file /tmp/cs/preparar-atributos.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Borrar productos primero: asi nadie queda apuntando a un atributo viejo. */
$ids     = wc_get_products( array( 'limit' => -1, 'return' => 'ids' ) );
$borrados = 0;
foreach ( $ids as $pid ) {
	wp_delete_post( $pid, true );
	++$borrados;
}
echo "Productos borrados: {$borrados}\n";

/* Categorias: se recrean desde el CSV (borra tambien las corruptas). */
foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) ) as $term ) {
	if ( 'uncategorized' === $term->slug ) {
		continue;
	}
	wp_delete_term( $term->term_id, 'product_cat' );
	echo "Categoria borrada: {$term->name}\n";
}

$esperado  = array(
	'paso'         => 'Paso',
	'tipo-de-piel' => 'Tipo de piel',
	'preocupacion' => 'Preocupación',
	'intensidad'   => 'Intensidad',
);
$conservar = array();

foreach ( wc_get_attribute_taxonomies() as $attr ) {
	$label = $attr->attribute_label ? $attr->attribute_label : $attr->attribute_name;
	$slug  = $attr->attribute_name;

	if ( isset( $esperado[ $slug ] ) && $esperado[ $slug ] === $label && ! isset( $conservar[ $slug ] ) ) {
		$conservar[ $slug ] = $attr->attribute_id;
		echo "Conservado: {$label} ({$slug})\n";
		continue;
	}
	wc_delete_attribute( $attr->attribute_id );
	echo "Borrado: {$label} ({$slug})\n";
}

foreach ( $esperado as $slug => $label ) {
	if ( isset( $conservar[ $slug ] ) ) {
		continue;
	}
	$id = wc_create_attribute(
		array(
			'name'         => $label,
			'slug'         => $slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);
	echo is_wp_error( $id ) ? "ERROR {$label}: " . $id->get_error_message() . "\n" : "Creado: {$label} ({$slug})\n";
}

echo "Atributos finales:\n";
foreach ( wc_get_attribute_taxonomies() as $attr ) {
	echo '  - ' . $attr->attribute_name . ' | ' . $attr->attribute_label . "\n";
}
