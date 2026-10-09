<?php
/**
 * Importa woocommerce/catalogo-clinical-skin.csv a WooCommerce.
 *
 * Uso:
 *   docker compose -f <compose> cp scripts\importar-catalogo.php cli:/tmp/importar-catalogo.php
 *   docker compose -f <compose> run --rm cli wp eval-file /tmp/importar-catalogo.php
 *
 * El CSV vive en el sitio estatico (montado en /static). Las fotos se bajan desde
 * el montaje /img de este stack; para eso se permite la red interna de Docker.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$origen   = ABSPATH . 'static/woocommerce/catalogo-clinical-skin.csv';
$temporal = '/tmp/catalogo-clinical-skin.csv';

$csv = @file_get_contents( $origen );
if ( false === $csv ) {
	echo "ERROR: no se pudo leer {$origen}\n";
	exit( 1 );
}

/* Rutas relativas del CSV -> URL interna del contenedor. */
$csv = str_replace( 'img/productos/', 'http://wordpress/img/productos/', $csv );
file_put_contents( $temporal, $csv );

/* wp_safe_remote_get bloquea IPs privadas: el importador las necesita aqui. */
add_filter( 'http_request_host_is_external', '__return_true', 15, 3 );

/* Atributos globales (el plan pide crearlos antes de importar). */
$atributos = array(
	'Paso'         => 'paso',
	'Tipo de piel' => 'tipo-de-piel',
	'Preocupación' => 'preocupacion',
	'Intensidad'   => 'intensidad',
);
foreach ( $atributos as $nombre => $slug ) {
	if ( wc_attribute_taxonomy_id_by_name( $slug ) ) {
		echo "Atributo ok: {$nombre} ({$slug})\n";
		continue;
	}
	$id = wc_create_attribute(
		array(
			'name'         => $nombre,
			'slug'         => $slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);
	echo is_wp_error( $id ) ? "ERROR atributo {$nombre}: " . $id->get_error_message() . "\n" : "Atributo creado: {$nombre}\n";
}

/* Encabezados del CSV -> claves del importador de WooCommerce. */
$mapeo = array(
	'ID'                      => 'id',
	'Type'                    => 'type',
	'SKU'                     => 'sku',
	'Name'                    => 'name',
	'Published'               => 'published',
	'Is featured?'            => 'featured',
	'Visibility in catalog'   => 'catalog_visibility',
	'Short description'       => 'short_description',
	'Description'             => 'description',
	'Tax status'              => 'tax_status',
	'Tax class'               => 'tax_class',
	'In stock?'               => 'stock_status',
	'Stock'                   => 'stock_quantity',
	'Backorders allowed?'     => 'backorders',
	'Sold individually?'      => 'sold_individually',
	'Allow customer reviews?' => 'reviews_allowed',
	'Purchase note'           => 'purchase_note',
	'Sale price'              => 'sale_price',
	'Regular price'           => 'regular_price',
	'Categories'              => 'category_ids',
	'Tags'                    => 'tag_ids',
	'Shipping class'          => 'shipping_class_id',
	'Images'                  => 'images',
	'Download limit'          => 'download_limit',
	'Download expiry days'    => 'download_expiry',
	'Parent'                  => 'parent_id',
	'Grouped products'        => 'grouped_products',
	'Upsells'                 => 'upsell_ids',
	'Cross-sells'             => 'cross_sell_ids',
	'External URL'            => 'product_url',
	'Position'                => 'menu_order',
);
foreach ( array( 1, 2, 3, 4 ) as $i ) {
	$mapeo[ 'Attribute ' . $i . ' name' ]     = 'attributes:name' . $i;
	$mapeo[ 'Attribute ' . $i . ' value(s)' ] = 'attributes:value' . $i;
	$mapeo[ 'Attribute ' . $i . ' visible' ]  = 'attributes:visible' . $i;
	$mapeo[ 'Attribute ' . $i . ' global' ]   = 'attributes:taxonomy' . $i;
}
foreach ( array( '_cs_paso', '_cs_agresividad', '_cs_frecuencia', '_cs_modo_uso', '_cs_ingredientes' ) as $meta ) {
	$mapeo[ 'Meta: ' . $meta ] = 'meta:' . $meta;
}

require_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';

$importador = new WC_Product_CSV_Importer(
	$temporal,
	array(
		'mapping'         => $mapeo,
		'parse'           => true,
		'update_existing' => false,
	)
);

$resultado = $importador->import();

printf(
	"Importados: %d | Actualizados: %d | Omitidos: %d | Fallidos: %d\n",
	count( (array) $resultado['imported'] ),
	count( (array) $resultado['updated'] ),
	count( (array) $resultado['skipped'] ),
	count( (array) $resultado['failed'] )
);
echo 'IDs: ' . implode( ', ', array_map( 'intval', (array) $resultado['imported'] ) ) . "\n";
foreach ( (array) $resultado['failed'] as $fallo ) {
	echo '  FALLO: ' . ( is_wp_error( $fallo ) ? $fallo->get_error_message() : ( is_object( $fallo ) && method_exists( $fallo, 'getMessage' ) ? $fallo->getMessage() : $fallo ) ) . "\n";
}
foreach ( (array) $resultado['skipped'] as $omitido ) {
	echo '  OMITIDO: ' . ( is_wp_error( $omitido ) ? $omitido->get_error_message() : $omitido ) . "\n";
}
