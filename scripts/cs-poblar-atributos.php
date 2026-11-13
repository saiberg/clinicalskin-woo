<?php
/**
 * Fase 1 — Modelo de datos cosmético en WooCommerce.
 *
 * 1. Amplía términos de atributos existentes (tipo-de-piel: +normal;
 *    preocupacion: +7 nuevas).
 * 2. Crea el atributo global "frecuencia" si falta.
 * 3. Puebla los 42 productos con atributos cosméticos (pa_paso,
 *    pa_tipo-de-piel, pa_preocupacion, pa_intensidad, pa_frecuencia)
 *    y metadatos de texto (cs_porque, cs_ficha, cs_modo_uso,
 *    cs_ingredientes, cs_desc).
 *
 * Los 11 productos del catálogo del test usan sus datos exactos.
 * Los 31 restantes se infieren de forma razonable por nombre/categoría.
 *
 * NO toca stock, precio, categorías ni imágenes.
 *
 * IMPORTANTE: si el atributo "frecuencia" no existía antes de ejecutar,
 * sus términos no se pueden asignar en el mismo proceso (WooCommerce
 * registra las taxonomías al inicializar). En ese caso ejecutar después
 * scripts/cs-fix-frecuencia.php.
 *
 * Uso: wp eval-file /tmp/cs/cs-poblar-atributos.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ini_set( 'memory_limit', '512M' );

/* ------------------------------------------------------------------ */
/* 1. Términos de atributos                                            */
/* ------------------------------------------------------------------ */

/** Crea un término en una taxonomía de atributo si no existe. */
function cs_attr_term( $tax, $nombre ) {
	$slug = sanitize_title( $nombre );
	$existe = get_term_by( 'slug', $slug, $tax );
	if ( $existe ) {
		return (int) $existe->term_id;
	}
	$alta = wp_insert_term( $nombre, $tax, array( 'slug' => $slug ) );
	if ( is_wp_error( $alta ) ) {
		$por_slug = get_term_by( 'slug', $slug, $tax );
		return $por_slug ? (int) $por_slug->term_id : 0;
	}
	return (int) $alta['term_id'];
}

/* Ampliar tipo-de-piel con "Normal". */
cs_attr_term( 'pa_tipo-de-piel', 'Normal' );

/* Ampliar preocupaciones con las 7 nuevas del prompt maestro. */
$preocupaciones_nuevas = array(
	'Hidratación',
	'Luminosidad',
	'Textura irregular',
	'Poros visibles',
	'Exceso de sebo',
	'Firmeza y elasticidad',
	'Sensibilidad y rojeces',
	'Contorno de ojos',
);
foreach ( $preocupaciones_nuevas as $p ) {
	cs_attr_term( 'pa_preocupacion', $p );
}

/* Crear atributo "frecuencia" si no existe. */
$existe_frecuencia = false;
foreach ( wc_get_attribute_taxonomies() as $a ) {
	if ( 'frecuencia' === $a->attribute_name ) {
		$existe_frecuencia = true;
		break;
	}
}
if ( ! $existe_frecuencia ) {
	$r = wc_create_attribute(
		array(
			'name'         => 'Frecuencia',
			'slug'         => 'frecuencia',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);
	echo is_wp_error( $r ) ? 'ERROR frecuencia: ' . $r->get_error_message() . "\n" : "Creado atributo frecuencia\n";
}
/* Refrescar caché de taxonomías de atributos. */
delete_transient( 'wc_attribute_taxonomies' );

/* Términos de frecuencia. */
$frecuencias = array( 'Mañana y noche', 'Solo de noche', 'Cada mañana', 'De noche', '2-3 veces por semana', 'Una vez por semana' );
foreach ( $frecuencias as $f ) {
	cs_attr_term( 'pa_frecuencia', $f );
}

echo "Términos de atributos ampliados.\n";

/* ------------------------------------------------------------------ */
/* 2. Datos por SKU                                                    */
/* ------------------------------------------------------------------ */

/*
 * Cada entrada: paso, tipos[], preocupaciones[], intensidad, frecuencia,
 * desc, porque, ficha, modo_uso, ingredientes.
 *
 * Los 11 primeros son los del catálogo del test (datos exactos).
 * El resto se infieren por nombre/categoría.
 */
$productos = array(

	/* ===== 11 productos verificados del catálogo del test ===== */
	'eucerin-gel-limpiador-dermopure-ultra-150ml' => array(
		'paso' => 'Limpieza', 'tipos' => array( 'Mixta', 'Grasa' ), 'preocupaciones' => array( 'Acné' ),
		'intensidad' => 'Media', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Gel limpiador de uso diario para piel grasa y con impurezas.',
		'porque' => 'Limpia el exceso de sebo de tu {tipoLargo} sin alterar la barrera: la base para que el resto de la rutina funcione.',
		'ficha' => 'Gel limpiador de textura transparente que retira impurezas, exceso de sebo y residuos del día sin resecar. Deja la piel fresca y equilibrada, sin sensación de tirantez.',
		'modo_uso' => 'Masajea una pequeña cantidad sobre piel húmeda, mañana y noche, durante 30 segundos. Enjuaga con agua tibia y seca con toques suaves.',
		'ingredientes' => 'Ácido salicílico, AHA suaves y glicerina.',
	),
	'avene-eau-agua-termal-avene-calmante-desensibilizante-y-suavizante-300' => array(
		'paso' => 'Limpieza', 'tipos' => array( 'Seca', 'Sensible' ), 'preocupaciones' => array( 'Deshidratación' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Agua termal en bruma: limpia y calma sin jabón.',
		'porque' => 'Calma y limpia tu {tipoLargo} sin jabón ni fricción: una limpieza que respeta la barrera.',
		'ficha' => 'Agua termal microfinada en bruma que limpia suavemente, desensibiliza y refresca. Ideal para pieles reactivas y como paso final de una rutina de doble limpieza.',
		'modo_uso' => 'Rocía sobre rostro y cuello mañana y noche y retira con un algodón suave o deja secar sin enjuagar.',
		'ingredientes' => 'Agua termal de Avène, rica en oligoelementos.',
	),
	'eucerin-crema-facial-dermopure-accion-intensiva-noche-40ml' => array(
		'paso' => 'Tratamiento', 'tipos' => array( 'Mixta', 'Grasa' ), 'preocupaciones' => array( 'Acné' ),
		'intensidad' => 'Media', 'frecuencia' => 'Solo de noche',
		'desc' => 'Tratamiento nocturno con hidroxiácidos para piel grasa con tendencia al acné.',
		'porque' => 'Los hidroxiácidos renuevan mientras duermes y regulan el sebo: ataca tu {preoc1Largo} sin sobretratar.',
		'ficha' => 'Crema de noche con complejo de hidroxiácidos al 10% (glicólico, salicílico y polihidroxi) que destapa poros, elimina células muertas y reduce granos, puntos negros y marcas post-acné mientras controla el brillo.',
		'modo_uso' => 'Solo de noche: aplica una vez al día sobre piel limpia y seca. De día, acompaña siempre con protector solar.',
		'ingredientes' => 'Complejo de hidroxiácidos al 10%, glicerina y pantenol.',
	),
	'vichy-mineral-89-serum-facial-hidratante-50ml' => array(
		'paso' => 'Tratamiento', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Deshidratación' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Booster hidratante con 89% de agua termal y ácido hialurónico.',
		'porque' => 'Ácido hialurónico que atrapa agua en tu {tipoLargo}: la respuesta directa a tu {preoc1Largo}.',
		'ficha' => 'Sérum gel con 89% de agua termal volcánica y ácido hialurónico de bajo peso molecular: fortalece la barrera, aporta luminosidad y deja la piel jugosa desde las primeras aplicaciones.',
		'modo_uso' => 'Aplica 2-3 gotas sobre piel ligeramente húmeda, mañana y noche, antes de tu crema.',
		'ingredientes' => 'Agua termal Vichy al 89% y ácido hialurónico.',
	),
	'vichy-liftactiv-serum-collagen-specialist-16' => array(
		'paso' => 'Tratamiento', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas', 'Edad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Sérum reafirmante con péptidos para densidad y luminosidad.',
		'porque' => 'Péptidos que estimulan el colágeno: trabajan tu {preoc1Largo} devolviendo densidad y luz.',
		'ficha' => 'Sérum con péptidos de colágeno y rhamnosa que mejora la densidad de la piel, suaviza líneas y aporta luminosidad con uso continuo. Textura fluida de rápida absorción.',
		'modo_uso' => 'Aplica 3-4 gotas por la mañana sobre rostro limpio, antes de la crema y del protector solar.',
		'ingredientes' => 'Péptidos de colágeno y rhamnosa.',
	),
	'eucerin-serum-facial-hyaluron-filler-elasticity-3d-30ml' => array(
		'paso' => 'Tratamiento', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Edad', 'Manchas' ),
		'intensidad' => 'Media', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Sérum concentrado con efecto relleno y efecto lifting.',
		'porque' => 'Ácido hialurónico de triple acción que rellena desde el interior: tu aliado contra tu {preoc1Largo}.',
		'ficha' => 'Sérum antiarrugas de alta concentración que combina ácido hialurónico con activos reafirmantes: rellena las arrugas visibles, mejora la elasticidad y reafirma el contorno facial con uso diario.',
		'modo_uso' => 'Aplica sobre rostro limpio mañana y noche, con un suave masaje ascendente, antes de la crema.',
		'ingredientes' => 'Ácido hialurónico y activos reafirmantes.',
	),
	'eucerin-crema-de-noche-hyaluron-filler-elasticity-facial-50ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Seca', 'Sensible' ), 'preocupaciones' => array( 'Deshidratación', 'Edad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Nutrición intensiva con ácido hialurónico y Arctiin.',
		'porque' => 'Nutrición y regeneración nocturna para tu {tipoLargo}: despiertas con la piel más elástica.',
		'ficha' => 'Crema de noche anti-edad para piel madura con ácido hialurónico de alto y bajo peso molecular, Arctiin y creatina: regenera durante el sueño, mejora la elasticidad y aporta hidratación intensiva con textura rica.',
		'modo_uso' => 'Aplica por la noche sobre rostro y cuello limpios; por la mañana, una avellana si tu piel lo pide.',
		'ingredientes' => 'Ácido hialurónico, Arctiin y creatina.',
	),
	'eucerin-repuesto-crema-facial-eucerin-hyaluron-filler-refill-dia-50ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Mixta', 'Grasa' ), 'preocupaciones' => array( 'Deshidratación', 'Edad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Hidratante de día de textura ligera, no obstruye poros.',
		'porque' => 'Hidratación ligera para tu {tipoLargo}: comodidad todo el día sin apagar la zona T.',
		'ficha' => 'Crema hidratante de textura ligera con ácido hialurónico que suaviza las líneas de deshidratación y deja la piel confortable sin sensación grasa. En formato recargable.',
		'modo_uso' => 'Aplica mañana y noche sobre rostro limpio, después del sérum y antes del protector solar.',
		'ingredientes' => 'Ácido hialurónico, glicerina y pantenol.',
	),
	'eucerin-protector-solar-hydro-fluid-facial-ultra-light-fps50-50ml' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas', 'Edad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Fluido facial ultraligero con FPS50+ de amplio espectro.',
		'porque' => 'Protección diaria: el mejor antienvejecimiento y el aliado de cualquier rutina.',
		'ficha' => 'Fluido solar de textura ultraligera con protección UVA/UVB FPS50+: no deja residuo blanco ni sensación grasa y sirve de último paso de la mañana. Resiste sudor y agua.',
		'modo_uso' => 'Último paso de la mañana, con generosidad (2 dedos para rostro y cuello). Reaplica cada 3 horas si te expones al sol.',
		'ingredientes' => 'Filtros UVA/UVB de amplio espectro y glicerina.',
	),
	'lrp-effaclar-gel-m-limpiador-purificante-400ml' => array(
		'paso' => 'Exfoliación', 'tipos' => array( 'Mixta', 'Grasa' ), 'preocupaciones' => array( 'Acné' ),
		'intensidad' => 'Media', 'frecuencia' => '2-3 veces por semana',
		'desc' => 'Gel exfoliante con LHA y AHA que desobstruye poros.',
		'porque' => 'Exfolía mientras duermes sin fricción: desobstruye poros y previene nuevos brotes.',
		'ficha' => 'Gel limpiador exfoliante con LHA y ácido glicólico que renueva la superficie, destapa los poros y afina la textura sin grano. Uso nocturno escalonado según tolerancia.',
		'modo_uso' => 'Noche: 2-3 veces por semana en lugar de tu limpiador habitual, dejando en contacto un minuto antes de enjuagar.',
		'ingredientes' => 'LHA y ácido glicólico, glicerina.',
	),
	'eucerin-unguento-reparador-aquaphor-55ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Seca', 'Sensible' ), 'preocupaciones' => array( 'Deshidratación' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Solo de noche',
		'desc' => 'Ungüento reparador de barrera para zonas muy secas o irritadas.',
		'porque' => 'Repara y sella durante la noche: ideal si tu piel tiende a reaccionar o a resecarse.',
		'ficha' => 'Ungüento reparador con panthenol, glicerina y bisabolol que forma una película protectora semioclusiva: acelera la regeneración de la piel seca o irritada y alivia la tirantez.',
		'modo_uso' => 'Solo de noche, en capa fina sobre zonas secas o como último paso de sellado en rostro y cuello.',
		'ingredientes' => 'Panthenol, glicerina y bisabolol.',
	),

	/* ===== 31 productos inferidos por nombre/categoría ===== */
	'vichy-shampoo-para-caida-de-cabello-vichy-dercos-400ml' => array(
		'paso' => 'Limpieza', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Firmeza y elasticidad' ),
		'intensidad' => 'Baja', 'frecuencia' => '2-3 veces por semana',
		'desc' => 'Shampoo anticaída para cabello debilitado.',
		'porque' => 'Fortalece el cabello y reduce la caída con uso constante.',
		'ficha' => 'Shampoo con activos fortalecedores que limpian suavemente y mejoran la resistencia del cabello.',
		'modo_uso' => 'Aplica sobre cabello húmedo, masajea el cuero cabelludo y enjuaga.',
		'ingredientes' => 'Activos fortalecedores.',
	),
	'vichy-dercos-vichy-shampoo-anti-caspa-para-cabello-graso-390ml' => array(
		'paso' => 'Limpieza', 'tipos' => array( 'Grasa' ), 'preocupaciones' => array( 'Exceso de sebo' ),
		'intensidad' => 'Media', 'frecuencia' => '2-3 veces por semana',
		'desc' => 'Shampoo anticaspa para cabello graso.',
		'porque' => 'Controla la caspa y el exceso de grasa del cuero cabelludo.',
		'ficha' => 'Shampoo anticaspa para cabello graso que purifica el cuero cabelludo y reduce la descamación.',
		'modo_uso' => 'Aplica sobre cabello húmedo, masajea y enjuaga. Repite si es necesario.',
		'ingredientes' => 'Activos anticaspa y purificantes.',
	),
	'avene-agua-termal-eau-thermale-avene-calmante-desensibilizante-y-suavi' => array(
		'paso' => 'Limpieza', 'tipos' => array( 'Seca', 'Sensible' ), 'preocupaciones' => array( 'Sensibilidad y rojeces' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Agua termal calmante en formato 150ml.',
		'porque' => 'Calma y refresca tu {tipoLargo} sin fricción.',
		'ficha' => 'Agua termal microfinada que calma, desensibiliza y refresca la piel reactiva.',
		'modo_uso' => 'Rocía sobre rostro y cuello y deja secar sin enjuagar.',
		'ingredientes' => 'Agua termal de Avène.',
	),
	'lrp-protector-solar-anthelios-uvair-vitamin-sun-stick-fps50-10ml' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Stick solar FPS50+ con vitamina.',
		'porque' => 'Protección portátil para retocar durante el día.',
		'ficha' => 'Stick solar de alta protección UVA/UVB, práctico para retoques y zonas puntuales.',
		'modo_uso' => 'Aplica sobre la piel antes de la exposición y retoca cada 2 horas.',
		'ingredientes' => 'Filtros solares de amplio espectro.',
	),
	'vichy-uv-aqua-protector-solar-facial-fps-50-50-ml' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Protector solar facial FPS50+ textura acuosa.',
		'porque' => 'Protección diaria ligera que no deja residuo.',
		'ficha' => 'Protector solar facial de textura acuosa con FPS50+ de amplio espectro.',
		'modo_uso' => 'Último paso de la mañana. Reaplica cada 3 horas.',
		'ingredientes' => 'Filtros UVA/UVB.',
	),
	'uriage-protector-solar-stick-bariesun-spf50-8g-uriage' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Stick solar SPF50 para zonas sensibles.',
		'porque' => 'Protección práctica para labios, nariz y contorno.',
		'ficha' => 'Stick solar SPF50 de alta protección para zonas puntuales.',
		'modo_uso' => 'Aplica antes de la exposición y retoca cada 2 horas.',
		'ingredientes' => 'Filtros solares de amplio espectro.',
	),
	'uriage-protector-solar-matificante-rostro-bariesun-fluido-spf50-50ml-u' => array(
		'paso' => 'Protección', 'tipos' => array( 'Mixta', 'Grasa' ), 'preocupaciones' => array( 'Exceso de sebo', 'Poros visibles' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Protector solar matificante SPF50 para rostro.',
		'porque' => 'Protege y controla el brillo de tu {tipoLargo}.',
		'ficha' => 'Fluido solar matificante SPF50 que protege y reduce el brillo.',
		'modo_uso' => 'Último paso de la mañana. Reaplica cada 3 horas.',
		'ingredientes' => 'Filtros UVA/UVB y activos matificantes.',
	),
	'uriage-protector-solar-bariesun-bruma-seca-spf50-200ml-uriage' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Bruma seca solar SPF50 para retoques.',
		'porque' => 'Retoca la protección sin tocar el maquillaje.',
		'ficha' => 'Bruma seca solar SPF50 de rápida absorción para retoques.',
		'modo_uso' => 'Rocía sobre rostro y cuerpo y retoca cada 2 horas.',
		'ingredientes' => 'Filtros solares de amplio espectro.',
	),
	'eucerin-protector-solar-eucerine-en-spray-transparente-de-toque-seco-2' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Protector solar en spray transparente de toque seco.',
		'porque' => 'Protección corporal cómoda y de rápida absorción.',
		'ficha' => 'Spray solar transparente de toque seco con alta protección.',
		'modo_uso' => 'Aplica sobre la piel antes de la exposición y retoca cada 2 horas.',
		'ingredientes' => 'Filtros UVA/UVB.',
	),
	'eucerin-protector-solar-corporal-oil-control-gel-crema-toque-seco-fps5' => array(
		'paso' => 'Protección', 'tipos' => array( 'Grasa' ), 'preocupaciones' => array( 'Exceso de sebo' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Protector solar corporal oil control FPS50.',
		'porque' => 'Protege y controla el brillo en piel con tendencia grasa.',
		'ficha' => 'Gel-crema solar corporal oil control de toque seco FPS50.',
		'modo_uso' => 'Aplica antes de la exposición y retoca cada 2 horas.',
		'ingredientes' => 'Filtros UVA/UVB y activos matificantes.',
	),
	'eucerin-locion-corporal-ph5-ligera-400ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Mixta', 'Grasa' ), 'preocupaciones' => array( 'Hidratación' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Loción corporal ligera pH5 para piel normal a grasa.',
		'porque' => 'Hidrata sin sensación grasa en piel que tiende al brillo.',
		'ficha' => 'Loción corporal ligera de pH5 que hidrata y respeta la barrera.',
		'modo_uso' => 'Aplica sobre la piel limpia y seca mañana y noche.',
		'ingredientes' => 'Glicerina y activos hidratantes.',
	),
	'eucerin-locion-corporal-ph5-hidratante-400ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Seca' ), 'preocupaciones' => array( 'Hidratación' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Loción corporal hidratante pH5 para piel seca.',
		'porque' => 'Hidratación intensa que calma la tirantez de tu {tipoLargo}.',
		'ficha' => 'Loción corporal hidratante de pH5 con textura nutritiva.',
		'modo_uso' => 'Aplica sobre la piel limpia y seca mañana y noche.',
		'ingredientes' => 'Glicerina y activos hidratantes.',
	),
	'eucerin-mist-spray-hyaluron-filler-3x-effect-hidratante-150ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Hidratación', 'Luminosidad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Bruma hidratante con ácido hialurónico.',
		'porque' => 'Refresca e hidrata al instante, ideal durante el día.',
		'ficha' => 'Bruma hidratante con ácido hialurónico de efecto luminoso.',
		'modo_uso' => 'Rocía sobre el rostro cuando necesites hidratación.',
		'ingredientes' => 'Ácido hialurónico y glicerina.',
	),
	'isdin-bloqueador-en-barra-isdin-protector-solar-invisible-stick-spf50' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Stick solar invisible SPF50.',
		'porque' => 'Protección práctica e invisible para retoques.',
		'ficha' => 'Stick solar invisible SPF50 de alta protección.',
		'modo_uso' => 'Aplica antes de la exposición y retoca cada 2 horas.',
		'ingredientes' => 'Filtros solares de amplio espectro.',
	),
	'isdin-protector-solar-facial-spf50-isdin-magic-age-repair' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas', 'Edad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Protector solar facial antiedad SPF50.',
		'porque' => 'Protege y aporta activos antiedad en un solo paso.',
		'ficha' => 'Protector solar facial SPF50 con activos reparadores y antiedad.',
		'modo_uso' => 'Último paso de la mañana. Reaplica cada 3 horas.',
		'ingredientes' => 'Filtros UVA/UVB y activos antiedad.',
	),
	'isdin-bloqueador-isdin-fusion-water-magic-spf50' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Protector solar fusion water SPF50 de textura acuosa.',
		'porque' => 'Protección ligera que se funde con la piel.',
		'ficha' => 'Protector solar de textura acuosa SPF50 de rápida absorción.',
		'modo_uso' => 'Último paso de la mañana. Reaplica cada 3 horas.',
		'ingredientes' => 'Filtros UVA/UVB.',
	),
	'isdin-gel-cream-adulto-spf50-250ml' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Gel-crema solar corporal SPF50.',
		'porque' => 'Protección corporal de textura fresca.',
		'ficha' => 'Gel-crema solar SPF50 para uso corporal.',
		'modo_uso' => 'Aplica antes de la exposición y retoca cada 2 horas.',
		'ingredientes' => 'Filtros UVA/UVB.',
	),
	'isdin-facial-mist-spf-50-bruma-facial-invisible-100ml' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Bruma facial invisible SPF50.',
		'porque' => 'Retoca la protección sin residuo.',
		'ficha' => 'Bruma facial invisible SPF50 para retoques.',
		'modo_uso' => 'Rocía sobre el rostro y retoca cada 2 horas.',
		'ingredientes' => 'Filtros solares de amplio espectro.',
	),
	'eucerin-repuesto-crema-facial-hyaluron-filler-refill-noche' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Seca', 'Sensible' ), 'preocupaciones' => array( 'Hidratación', 'Edad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Solo de noche',
		'desc' => 'Repuesto de crema de noche con ácido hialurónico.',
		'porque' => 'Regenera e hidrata durante la noche.',
		'ficha' => 'Crema de noche con ácido hialurónico en formato recargable.',
		'modo_uso' => 'Aplica por la noche sobre rostro limpio.',
		'ingredientes' => 'Ácido hialurónico y activos hidratantes.',
	),
	'eucerin-unguento-reparador-aquaphor-89ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Seca', 'Sensible' ), 'preocupaciones' => array( 'Deshidratación' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Solo de noche',
		'desc' => 'Ungüento reparador de barrera 89ml.',
		'porque' => 'Repara zonas muy secas o irritadas.',
		'ficha' => 'Ungüento reparador semioclusivo para piel seca o irritada.',
		'modo_uso' => 'Aplica en capa fina sobre zonas secas.',
		'ingredientes' => 'Panthenol, glicerina y bisabolol.',
	),
	'eucerin-protector-solar-serum-oil-control-uv-diario-ultra-ligero-fps-5' => array(
		'paso' => 'Protección', 'tipos' => array( 'Mixta', 'Grasa' ), 'preocupaciones' => array( 'Exceso de sebo', 'Poros visibles' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Sérum solar oil control FPS50+ ultra ligero.',
		'porque' => 'Protege y controla el brillo en piel grasa.',
		'ficha' => 'Sérum solar oil control FPS50+ de textura ultraligera.',
		'modo_uso' => 'Último paso de la mañana. Reaplica cada 3 horas.',
		'ingredientes' => 'Filtros UVA/UVB y activos matificantes.',
	),
	'eucerin-serum-facial-epigenetic-anti-edad-30ml' => array(
		'paso' => 'Tratamiento', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Edad', 'Firmeza y elasticidad' ),
		'intensidad' => 'Media', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Sérum anti-edad epigenético.',
		'porque' => 'Trabaja la firmeza y las líneas de expresión de tu {tipoLargo}.',
		'ficha' => 'Sérum anti-edad con activos epigenéticos que mejoran firmeza y textura.',
		'modo_uso' => 'Aplica sobre rostro limpio mañana y noche antes de la crema.',
		'ingredientes' => 'Activos epigenéticos y péptidos.',
	),
	'eucerin-crema-de-noche-hyaluron-filler-3x-effect-facial-50ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Edad', 'Hidratación' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Solo de noche',
		'desc' => 'Crema de noche anti-edad 3x effect.',
		'porque' => 'Regenera e hidrata profundamente durante la noche.',
		'ficha' => 'Crema de noche con ácido hialurónico de triple acción.',
		'modo_uso' => 'Aplica por la noche sobre rostro limpio.',
		'ingredientes' => 'Ácido hialurónico y activos antiedad.',
	),
	'eucerin-crema-de-dia-hyaluron-filler-3x-effect-facial-fps30-50ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Edad', 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Crema de día anti-edad con FPS30.',
		'porque' => 'Hidrata, previene y protege en un solo paso.',
		'ficha' => 'Crema de día con ácido hialurónico y FPS30.',
		'modo_uso' => 'Aplica por la mañana sobre rostro limpio.',
		'ingredientes' => 'Ácido hialurónico y filtros solares.',
	),
	'eucerin-protector-solar-ninos-locion-corporal-fps50-400ml' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Protector solar infantil FPS50+ 400ml.',
		'porque' => 'Protección alta y suave para piel infantil.',
		'ficha' => 'Loción solar infantil FPS50+ de alta tolerancia.',
		'modo_uso' => 'Aplica antes de la exposición y retoca cada 2 horas.',
		'ingredientes' => 'Filtros UVA/UVB.',
	),
	'eucerin-protector-solar-ninos-locion-corporal-fps50-200ml' => array(
		'paso' => 'Protección', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Protector solar infantil FPS50+ 200ml.',
		'porque' => 'Protección alta y suave para piel infantil.',
		'ficha' => 'Loción solar infantil FPS50+ de alta tolerancia.',
		'modo_uso' => 'Aplica antes de la exposición y retoca cada 2 horas.',
		'ingredientes' => 'Filtros UVA/UVB.',
	),
	'eucerin-contorno-de-ojos-eucerin-antiarrugas-hyaluron-filler-15ml' => array(
		'paso' => 'Tratamiento', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Contorno de ojos', 'Edad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Contorno de ojos antiarrugas con ácido hialurónico.',
		'porque' => 'Suaviza líneas del contorno de ojos y aporta hidratación.',
		'ficha' => 'Crema de contorno de ojos con ácido hialurónico antiarrugas.',
		'modo_uso' => 'Aplica con toques suaves alrededor del ojo mañana y noche.',
		'ingredientes' => 'Ácido hialurónico.',
	),
	'eucerin-hyaluron-filler-elasticity-eyes-crema-contorno-de-ojos-antiarr' => array(
		'paso' => 'Tratamiento', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Contorno de ojos', 'Firmeza y elasticidad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Mañana y noche',
		'desc' => 'Contorno de ojos antiedad con elasticidad.',
		'porque' => 'Mejora firmeza y elasticidad del contorno de ojos.',
		'ficha' => 'Contorno de ojos con ácido hialurónico y activos de elasticidad.',
		'modo_uso' => 'Aplica con toques suaves alrededor del ojo mañana y noche.',
		'ingredientes' => 'Ácido hialurónico y activos de elasticidad.',
	),
	'eucerin-unguento-reparador-aquaphor-corporal-220ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Seca', 'Sensible' ), 'preocupaciones' => array( 'Deshidratación' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Solo de noche',
		'desc' => 'Ungüento reparador corporal 220ml.',
		'porque' => 'Repara la piel corporal muy seca o irritada.',
		'ficha' => 'Ungüento reparador corporal semioclusivo.',
		'modo_uso' => 'Aplica en capa fina sobre zonas secas.',
		'ingredientes' => 'Panthenol, glicerina y bisabolol.',
	),
	'eucerin-crema-de-dia-hyaluron-filler-elasticity-facial-50ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Edad', 'Firmeza y elasticidad' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Crema de día anti-edad con elasticidad.',
		'porque' => 'Hidrata y mejora la elasticidad de tu {tipoLargo}.',
		'ficha' => 'Crema de día con ácido hialurónico y activos de elasticidad.',
		'modo_uso' => 'Aplica por la mañana sobre rostro limpio.',
		'ingredientes' => 'Ácido hialurónico y activos de elasticidad.',
	),
	'eucerin-crema-de-dia-hyaluron-filler-elasticity-facial-fps30-50ml' => array(
		'paso' => 'Hidratación', 'tipos' => array( 'Todo' ), 'preocupaciones' => array( 'Edad', 'Manchas' ),
		'intensidad' => 'Baja', 'frecuencia' => 'Cada mañana',
		'desc' => 'Crema de día anti-edad con FPS30.',
		'porque' => 'Hidrata, mejora elasticidad y protege del sol.',
		'ficha' => 'Crema de día con ácido hialurónico, activos de elasticidad y FPS30.',
		'modo_uso' => 'Aplica por la mañana sobre rostro limpio.',
		'ingredientes' => 'Ácido hialurónico, activos de elasticidad y filtros solares.',
	),
);

echo 'Productos definidos en el mapa: ' . count( $productos ) . "\n";

/* ------------------------------------------------------------------ */
/* 3. Poblar productos                                                 */
/* ------------------------------------------------------------------ */

$ids = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => array( 'publish' ) ) );
$poblados = 0;
$sin_datos = array();

foreach ( $ids as $id ) {
	$p    = wc_get_product( $id );
	$sku  = $p->get_sku();
	$datos = isset( $productos[ $sku ] ) ? $productos[ $sku ] : null;

	if ( ! $datos ) {
		$sin_datos[] = $sku . ' (' . $p->get_name() . ')';
		continue;
	}

	/* Atributos taxonómicos. */
	$atributos = array();

	$paso_id = get_term_by( 'slug', sanitize_title( $datos['paso'] ), 'pa_paso' );
	if ( $paso_id ) {
		$atributos[] = array( 'pa_paso', $paso_id->term_id );
	}

	foreach ( $datos['tipos'] as $tipo ) {
		$t = get_term_by( 'slug', sanitize_title( $tipo ), 'pa_tipo-de-piel' );
		if ( $t ) {
			$atributos[] = array( 'pa_tipo-de-piel', $t->term_id );
		}
	}
	foreach ( $datos['preocupaciones'] as $preoc ) {
		$t = get_term_by( 'slug', sanitize_title( $preoc ), 'pa_preocupacion' );
		if ( $t ) {
			$atributos[] = array( 'pa_preocupacion', $t->term_id );
		}
	}
	$int_id = get_term_by( 'slug', sanitize_title( $datos['intensidad'] ), 'pa_intensidad' );
	if ( $int_id ) {
		$atributos[] = array( 'pa_intensidad', $int_id->term_id );
	}
	$frec_id = get_term_by( 'slug', sanitize_title( $datos['frecuencia'] ), 'pa_frecuencia' );
	if ( $frec_id ) {
		$atributos[] = array( 'pa_frecuencia', $frec_id->term_id );
	}

	/* Agrupar por taxonomía. */
	$por_tax = array();
	foreach ( $atributos as $par ) {
		$por_tax[ $par[0] ][] = $par[1];
	}

	$wc_atts = array();
	foreach ( $por_tax as $tax => $terms ) {
		$attr = new WC_Product_Attribute();
		$attr->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
		$attr->set_name( $tax );
		$attr->set_options( $terms );
		$attr->set_position( 0 );
		$attr->set_visible( false );
		$attr->set_variation( false );
		$wc_atts[] = $attr;
	}
	$p->set_attributes( $wc_atts );
	$p->save();

	/* Metadatos de texto. */
	update_post_meta( $id, 'cs_desc', $datos['desc'] );
	update_post_meta( $id, 'cs_porque', $datos['porque'] );
	update_post_meta( $id, 'cs_ficha', $datos['ficha'] );
	update_post_meta( $id, 'cs_modo_uso', $datos['modo_uso'] );
	update_post_meta( $id, 'cs_ingredientes', $datos['ingredientes'] );

	++$poblados;
}

echo "Productos poblados: {$poblados}\n";
if ( $sin_datos ) {
	echo "Sin datos cosméticos (quedan solo con datos nativos):\n";
	foreach ( $sin_datos as $s ) {
		echo '  - ' . $s . "\n";
	}
}

/* Limpiar cachés. */
wc_delete_product_transients();
delete_transient( 'wc_attribute_taxonomies' );

echo "Fase 1 completada.\n";
