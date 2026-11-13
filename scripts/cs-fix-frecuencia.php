<?php
/**
 * Corrige la asignación de pa_frecuencia (Fase 2, fix).
 * La taxonomía existe pero quedó sin términos ni asignaciones porque
 * el script original la creó en el mismo proceso sin refrescar el registro.
 *
 * Uso: wp eval-file /tmp/cs/cs-fix-frecuencia.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Mapa SKU → frecuencia (mismo criterio del script de Fase 1). */
$frecuencias = array(
	'eucerin-gel-limpiador-dermopure-ultra-150ml' => 'Mañana y noche',
	'avene-eau-agua-termal-avene-calmante-desensibilizante-y-suavizante-300' => 'Mañana y noche',
	'eucerin-crema-facial-dermopure-accion-intensiva-noche-40ml' => 'Solo de noche',
	'vichy-mineral-89-serum-facial-hidratante-50ml' => 'Mañana y noche',
	'vichy-liftactiv-serum-collagen-specialist-16' => 'Mañana y noche',
	'eucerin-serum-facial-hyaluron-filler-elasticity-3d-30ml' => 'Mañana y noche',
	'eucerin-crema-de-noche-hyaluron-filler-elasticity-facial-50ml' => 'Mañana y noche',
	'eucerin-repuesto-crema-facial-eucerin-hyaluron-filler-refill-dia-50ml' => 'Mañana y noche',
	'eucerin-protector-solar-hydro-fluid-facial-ultra-light-fps50-50ml' => 'Cada mañana',
	'lrp-effaclar-gel-m-limpiador-purificante-400ml' => '2-3 veces por semana',
	'eucerin-unguento-reparador-aquaphor-55ml' => 'Solo de noche',
	'vichy-shampoo-para-caida-de-cabello-vichy-dercos-400ml' => '2-3 veces por semana',
	'vichy-dercos-vichy-shampoo-anti-caspa-para-cabello-graso-390ml' => '2-3 veces por semana',
	'avene-agua-termal-eau-thermale-avene-calmante-desensibilizante-y-suavi' => 'Mañana y noche',
	'lrp-protector-solar-anthelios-uvair-vitamin-sun-stick-fps50-10ml' => 'Cada mañana',
	'vichy-uv-aqua-protector-solar-facial-fps-50-50-ml' => 'Cada mañana',
	'uriage-protector-solar-stick-bariesun-spf50-8g-uriage' => 'Cada mañana',
	'uriage-protector-solar-matificante-rostro-bariesun-fluido-spf50-50ml-u' => 'Cada mañana',
	'uriage-protector-solar-bariesun-bruma-seca-spf50-200ml-uriage' => 'Cada mañana',
	'eucerin-protector-solar-eucerine-en-spray-transparente-de-toque-seco-2' => 'Cada mañana',
	'eucerin-protector-solar-corporal-oil-control-gel-crema-toque-seco-fps5' => 'Cada mañana',
	'eucerin-locion-corporal-ph5-ligera-400ml' => 'Mañana y noche',
	'eucerin-locion-corporal-ph5-hidratante-400ml' => 'Mañana y noche',
	'eucerin-mist-spray-hyaluron-filler-3x-effect-hidratante-150ml' => 'Mañana y noche',
	'isdin-bloqueador-en-barra-isdin-protector-solar-invisible-stick-spf50' => 'Cada mañana',
	'isdin-protector-solar-facial-spf50-isdin-magic-age-repair' => 'Cada mañana',
	'isdin-bloqueador-isdin-fusion-water-magic-spf50' => 'Cada mañana',
	'isdin-gel-cream-adulto-spf50-250ml' => 'Cada mañana',
	'isdin-facial-mist-spf-50-bruma-facial-invisible-100ml' => 'Cada mañana',
	'eucerin-repuesto-crema-facial-hyaluron-filler-refill-noche' => 'Solo de noche',
	'eucerin-unguento-reparador-aquaphor-89ml' => 'Solo de noche',
	'eucerin-protector-solar-serum-oil-control-uv-diario-ultra-ligero-fps-5' => 'Cada mañana',
	'eucerin-serum-facial-epigenetic-anti-edad-30ml' => 'Mañana y noche',
	'eucerin-crema-de-noche-hyaluron-filler-3x-effect-facial-50ml' => 'Solo de noche',
	'eucerin-crema-de-dia-hyaluron-filler-3x-effect-facial-fps30-50ml' => 'Cada mañana',
	'eucerin-protector-solar-ninos-locion-corporal-fps50-400ml' => 'Cada mañana',
	'eucerin-protector-solar-ninos-locion-corporal-fps50-200ml' => 'Cada mañana',
	'eucerin-contorno-de-ojos-eucerin-antiarrugas-hyaluron-filler-15ml' => 'Mañana y noche',
	'eucerin-hyaluron-filler-elasticity-eyes-crema-contorno-de-ojos-antiarr' => 'Mañana y noche',
	'eucerin-unguento-reparador-aquaphor-corporal-220ml' => 'Solo de noche',
	'eucerin-crema-de-dia-hyaluron-filler-elasticity-facial-50ml' => 'Cada mañana',
	'eucerin-crema-de-dia-hyaluron-filler-elasticity-facial-fps30-50ml' => 'Cada mañana',
);

/* 1. Crear los términos de frecuencia. */
$terminos = array();
foreach ( array_unique( array_values( $frecuencias ) ) as $nombre ) {
	$slug = sanitize_title( $nombre );
	$term = get_term_by( 'slug', $slug, 'pa_frecuencia' );
	if ( ! $term ) {
		$r = wp_insert_term( $nombre, 'pa_frecuencia', array( 'slug' => $slug ) );
		if ( is_wp_error( $r ) ) {
			echo "ERROR término {$nombre}: " . $r->get_error_message() . "\n";
			continue;
		}
		$terminos[ $nombre ] = (int) $r['term_id'];
	} else {
		$terminos[ $nombre ] = (int) $term->term_id;
	}
}
echo 'Términos de frecuencia: ' . count( $terminos ) . "\n";

/* 2. Asignar a cada producto (respetando los atributos existentes). */
$attr_id = wc_attribute_taxonomy_id_by_name( 'frecuencia' );
echo "attribute_taxonomy_id frecuencia: {$attr_id}\n";

$asignados = 0;
foreach ( $frecuencias as $sku => $nombre ) {
	$pid = wc_get_product_id_by_sku( $sku );
	if ( ! $pid ) {
		echo "SKU no encontrado: {$sku}\n";
		continue;
	}
	$term_id = isset( $terminos[ $nombre ] ) ? $terminos[ $nombre ] : 0;
	if ( ! $term_id ) {
		continue;
	}
	$p = wc_get_product( $pid );

	/* Reconstruir atributos: los existentes + frecuencia. */
	$nuevos = array();
	foreach ( $p->get_attributes() as $tax => $attr ) {
		if ( 'pa_frecuencia' === $tax ) {
			continue;
		}
		$nuevos[ $tax ] = $attr;
	}
	$frec_attr = new WC_Product_Attribute();
	$frec_attr->set_id( $attr_id );
	$frec_attr->set_name( 'pa_frecuencia' );
	$frec_attr->set_options( array( $term_id ) );
	$frec_attr->set_position( 0 );
	$frec_attr->set_visible( false );
	$frec_attr->set_variation( false );
	$nuevos['pa_frecuencia'] = $frec_attr;

	$p->set_attributes( $nuevos );
	$p->save();
	++$asignados;
}

echo "Productos con frecuencia asignada: {$asignados}\n";
wc_delete_product_transients();
echo "Fix completado.\n";
