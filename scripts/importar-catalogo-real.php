<?php
/**
 * Importa el inventario real (static/woocommerce/inventarioparaweb.csv) a WooCommerce.
 *
 * Reglas acordadas:
 *  - reemplaza el catalogo demo (los productos que no estan en el CSV van a la papelera)
 *  - SKU generado, stock 1 unidad, categorias por marca y por tipo
 *  - descripcion corta = primera frase, peso 0.5 sin medidas (igual que el demo)
 *
 * Uso:
 *   docker compose -f <compose> run --rm cli wp eval-file /tmp/cs/importar-catalogo-real.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

ini_set( 'memory_limit', '512M' );

define( 'CS_CSV', ABSPATH . 'static/woocommerce/inventarioparaweb.csv' );
define( 'CS_IMG', ABSPATH . 'static/woocommerce/productosconlogo' );

/* ---------------------------------------------------------------- utilidades */

function csimp_slug( $texto ) {
	$texto = remove_accents( (string) $texto );
	$texto = strtolower( $texto );
	$texto = preg_replace( '/[^a-z0-9]+/', '-', $texto );
	return trim( (string) $texto, '-' );
}

function cs_precio( $bruto ) {
	$solo = preg_replace( '/[^0-9]/', '', (string) $bruto );
	return '' === $solo ? 0 : (int) $solo;
}

function cs_primera_frase( $texto ) {
	$texto = trim( preg_replace( '/\s+/', ' ', (string) $texto ) );
	if ( '' === $texto ) {
		return '';
	}
	if ( preg_match( '/^(.{20,180}?\.)\s/s', $texto, $m ) ) {
		return trim( $m[1] );
	}
	return strlen( $texto ) > 180 ? rtrim( substr( $texto, 0, 180 ) ) . '...' : $texto;
}

/* Clasifica por tipo a partir del nombre (sin acentos, minusculas). */
function cs_tipo( $nombre ) {
	$n = ' ' . csimp_slug( $nombre ) . ' ';
	if ( false !== strpos( $n, 'contorno' ) ) {
		return 'Contorno de ojos';
	}
	if ( preg_match( '/balsamo|labial/', $n ) ) {
		return 'Labios';
	}
	if ( preg_match( '/protector|eritro|\bsun\b/', $n ) ) {
		return 'Protector solar';
	}
	if ( preg_match( '/micelar|limpiador|limpieza|espuma|exfoliante|peeling|scrub/', $n ) ) {
		return 'Limpiador';
	}
	if ( preg_match( '/serum|suero|booster/', $n ) ) {
		return 'Sérum';
	}
	if ( false !== strpos( $n, 'tonic' ) ) {
		return 'Tónico';
	}
	if ( preg_match( '/mascarilla|\bmask\b/', $n ) ) {
		return 'Mascarilla';
	}
	if ( preg_match( '/champu|shampoo/', $n ) ) {
		return 'Champú';
	}
	if ( preg_match( '/unguento|corporal/', $n ) ) {
		return 'Cuidado corporal';
	}
	if ( preg_match( '/\bcrema\b|\bgel\b|emulsion/', $n ) ) {
		return 'Crema';
	}
	return 'Otros';
}

function cs_mime( $ruta ) {
	$ext  = strtolower( pathinfo( $ruta, PATHINFO_EXTENSION ) );
	$mims = array(
		'png'  => 'image/png',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'webp' => 'image/webp',
		'gif'  => 'image/gif',
	);
	return isset( $mims[ $ext ] ) ? $mims[ $ext ] : 'image/jpeg';
}

function cs_termino( $nombre ) {
	if ( '' === trim( $nombre ) ) {
		return 0;
	}
	$existe = term_exists( $nombre, 'product_cat' );
	if ( $existe ) {
		return (int) $existe['term_id'];
	}
	$alta = wp_insert_term( $nombre, 'product_cat' );
	if ( is_wp_error( $alta ) ) {
		$por_slug = get_term_by( 'slug', csimp_slug( $nombre ), 'product_cat' );
		return $por_slug ? (int) $por_slug->term_id : 0;
	}
	return (int) $alta['term_id'];
}

function cs_adjuntar( $ruta, $titulo ) {
	static $listo = false;
	if ( ! $listo ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$listo = true;
	}
	$bits = wp_upload_bits( basename( $ruta ), null, file_get_contents( $ruta ) );
	if ( ! empty( $bits['error'] ) ) {
		return 0;
	}
	$adj = wp_insert_attachment(
		array(
			'post_mime_type' => cs_mime( $ruta ),
			'post_title'     => $titulo,
			'post_status'    => 'inherit',
			'guid'           => $bits['url'],
		),
		$bits['file'],
		0
	);
	if ( is_wp_error( $adj ) || ! $adj ) {
		return 0;
	}
	wp_update_attachment_metadata( $adj, wp_generate_attachment_metadata( $adj, $bits['file'] ) );
	return (int) $adj;
}

/* ------------------------------------------------------- mapa de imagenes */

$mapa       = array();
$mapa_plano = array();

$cs_rutas = array();
@exec( 'find ' . escapeshellarg( CS_IMG ) . ' -type f 2>/dev/null', $cs_rutas );
if ( ! $cs_rutas ) {
	/* Respaldo: sobre este montaje el listado de PHP puede venir truncado. */
	$iter = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( CS_IMG, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $iter as $f ) {
		if ( $f->isFile() ) {
			$cs_rutas[] = $f->getPathname();
		}
	}
}
foreach ( $cs_rutas as $ruta ) {
	$nombre = basename( $ruta );
	if ( preg_match( '/\.(png|jpe?g|webp|gif)$/i', $nombre ) ) {
		$mapa[ mb_strtolower( $nombre, 'UTF-8' ) ]                          = $ruta;
		$mapa_plano[ mb_strtolower( remove_accents( $nombre ), 'UTF-8' ) ] = $ruta;
	}
}
printf( "Imagenes disponibles: %d\n", count( $mapa ) );

/* ------------------------------------------------------------- leer el CSV */

$mano = @fopen( CS_CSV, 'r' );
if ( ! $mano ) {
	echo 'ERROR: no se pudo leer ' . CS_CSV . "\n";
	exit( 1 );
}

$cab = fgetcsv( $mano, 0, ';', '"', '' );
if ( ! is_array( $cab ) ) {
	echo "ERROR: CSV vacio\n";
	exit( 1 );
}

$idx = array();
foreach ( range( 0, count( $cab ) - 1 ) as $i ) {
	$celda = preg_replace( '/^\xEF\xBB\xBF/', '', (string) $cab[ $i ] );
	$idx[ trim( $celda ) ] = $i;
}
$buscar_col = function ( $contiene ) use ( $idx ) {
	foreach ( $idx as $nombre => $i ) {
		if ( false !== stripos( $nombre, $contiene ) ) {
			return $i;
		}
	}
	return -1;
};
$im = $buscar_col( 'MARCA' );
$ip = $buscar_col( 'PRODUCTO' );
$id = $buscar_col( 'DESCRIPCION' );
$ia = $buscar_col( 'ARCHIVO' );
$ic = $buscar_col( 'PRECIO' );
if ( min( $im, $ip, $id, $ia, $ic ) < 0 ) {
	echo "ERROR: el CSV no trae las columnas esperadas (MARCA/PRODUCTO/DESCRIPCION/NOMBRE_ARCHIVO/PRECIO_VENTA)\n";
	exit( 1 );
}

$renglones   = array();
$reparadas   = 0;
$duplicados  = array();
$sin_precio  = array();
$usados      = array();
$filas       = 0;

while ( ( $linea = fgetcsv( $mano, 0, ';', '"', '' ) ) !== false ) {
	$linea = array_map(
		function ( $v ) {
			return trim( (string) $v );
		},
		$linea
	);
	if ( '' === implode( '', $linea ) ) {
		continue;
	}

	$marca      = $linea[ $im ] ?? '';
	$producto   = $linea[ $ip ] ?? '';
	$desc       = $linea[ $id ] ?? '';
	$archivo    = $linea[ $ia ] ?? '';
	$precio_txt = $linea[ $ic ] ?? '';

	/* Filas con ':' en vez de ';' antes de la descripcion: las columnas vienen corridas. */
	if ( ! preg_match( '/\.(png|jpe?g|webp|gif)$/i', $archivo ) && preg_match( '/\.(png|jpe?g|webp|gif)$/i', $desc ) ) {
		$reparadas++;
		$precio_txt = $archivo;
		$archivo    = $desc;
		if ( preg_match( '/^(.*?)\s*:\s*"?(.*)$/s', $producto, $p ) ) {
			$producto = trim( $p[1] );
			$desc     = trim( $p[2], " \t\n\r\0\x0B\"" );
		} else {
			$desc = '';
		}
	}

	if ( '' === $marca || '' === $producto ) {
		continue;
	}

	$precio = cs_precio( $precio_txt );
	$clave  = mb_strtolower( preg_replace( '/\s+/', ' ', $producto ), 'UTF-8' );

	if ( isset( $usados[ $clave ] ) ) {
		$duplicados[] = $producto . ' (precios: ' . $usados[ $clave ] . ' / ' . $precio_txt . ')';
		continue;
	}
	if ( $precio <= 0 ) {
		$sin_precio[] = $marca . ' | ' . $producto . ' (' . $precio_txt . ')';
	}
	$usados[ $clave ] = $precio_txt;

	$renglones[] = array(
		'marca'    => $marca,
		'producto' => $producto,
		'desc'     => $desc,
		'archivo'  => $archivo,
		'precio'   => $precio,
		'tipo'     => cs_tipo( $producto ),
	);
	$filas++;
}
fclose( $mano );

printf( "Filas validas: %d | filas reparadas (dos puntos): %d | duplicadas omitidas: %d\n", $filas, $reparadas, count( $duplicados ) );

if ( 0 === $filas ) {
	echo "ERROR: no se leyo ninguna fila\n";
	exit( 1 );
}

/* ------------------------------------------------------------- importacion */

$antes = wc_get_products( array( 'limit' => -1, 'return' => 'ids', 'status' => 'any' ) );
printf( "Productos en la tienda antes: %d\n", count( $antes ) );

$creados        = 0;
$actualizados   = 0;
$tocados        = array();
$faltantes      = array();
$skus_run       = array();
$referenciados  = array();

foreach ( $renglones as $r ) {
	$sin_marca = preg_replace( '/^' . preg_quote( $r['marca'], '/' ) . '\s+/i', '', $r['producto'] );
	$sku       = csimp_slug( $r['marca'] . ' ' . $sin_marca );
	if ( strlen( $sku ) > 70 ) {
		$sku = rtrim( substr( $sku, 0, 70 ), '-' );
	}
	$base = $sku;
	$k    = 2;
	while ( isset( $skus_run[ $sku ] ) ) {
		$sku = $base . '-' . $k;
		$k++;
	}
	$skus_run[ $sku ] = true;

	$existente = wc_get_product_id_by_sku( $sku );
	if ( $existente ) {
		$p = wc_get_product( $existente );
		if ( ! $p ) {
			$p = new WC_Product_Simple();
			$creados++;
		} else {
			$actualizados++;
		}
	} else {
		$p = new WC_Product_Simple();
		$creados++;
	}

	$p->set_name( $r['producto'] );
	$p->set_status( 'publish' );
	$p->set_catalog_visibility( 'visible' );
	$p->set_sku( $sku );
	$p->set_description( $r['desc'] );
	$p->set_short_description( cs_primera_frase( $r['desc'] ) );
	$p->set_regular_price( (string) $r['precio'] );
	$p->set_manage_stock( true );
	$p->set_stock_quantity( 1 );
	$p->set_stock_status( 'instock' );
	$p->set_backorders( 'no' );
	$p->set_weight( '0.5' );
	$p->set_tax_status( 'taxable' );
	$p->set_tax_class( '' );

	$cats = array_filter( array( cs_termino( $r['marca'] ), cs_termino( $r['tipo'] ) ) );
	$p->set_category_ids( array_values( $cats ) );
	$referenciados = array_merge( $referenciados, $cats );

	$clave_img = mb_strtolower( trim( $r['archivo'] ), 'UTF-8' );
	$ruta      = '';
	if ( isset( $mapa[ $clave_img ] ) ) {
		$ruta = $mapa[ $clave_img ];
	} elseif ( isset( $mapa_plano[ mb_strtolower( remove_accents( $clave_img ), 'UTF-8' ) ] ) ) {
		$ruta = $mapa_plano[ mb_strtolower( remove_accents( $clave_img ), 'UTF-8' ) ];
	}
	if ( '' === $ruta ) {
		$faltantes[] = $r['producto'] . ' -> ' . $r['archivo'];
	} elseif ( ! $p->get_image_id() ) {
		$adj = cs_adjuntar( $ruta, $r['producto'] );
		if ( $adj ) {
			$p->set_image_id( $adj );
		}
	}

	$p->save();
	$tocados[] = (int) $p->get_id();
}

/* ------------------------------------------------ papelera y limpieza final */

$borrados = 0;
foreach ( $antes as $id ) {
	if ( in_array( (int) $id, $tocados, true ) ) {
		continue;
	}
	if ( wp_trash_post( (int) $id ) ) {
		$borrados++;
	}
}

$terminos_vacios = 0;
$referenciados   = array_unique( $referenciados );
foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) ) as $t ) {
	if ( $t->count > 0 || in_array( (int) $t->term_id, $referenciados, true ) ) {
		continue;
	}
	if ( wp_delete_term( (int) $t->term_id, 'product_cat' ) ) {
		$terminos_vacios++;
	}
}

/* --------------------------------------------------------------- informe */

printf( "\nCreados: %d | Actualizados: %d | Papelera (demo): %d | Terminos vacios borrados: %d\n", $creados, $actualizados, $borrados, $terminos_vacios );

$marchas_todo = array();
$tipos_todo   = array();
foreach ( $renglones as $r ) {
	$marchas_todo[ $r['marca'] ] = ( isset( $marchas_todo[ $r['marca'] ] ) ? $marchas_todo[ $r['marca'] ] : 0 ) + 1;
	$tipos_todo[ $r['tipo'] ]    = ( isset( $tipos_todo[ $r['tipo'] ] ) ? $tipos_todo[ $r['tipo'] ] : 0 ) + 1;
}
arsort( $marchas_todo );
arsort( $tipos_todo );
$lista = function ( $arr ) {
	$partes = array();
	foreach ( $arr as $nombre => $n ) {
		$partes[] = $nombre . ' (' . $n . ')';
	}
	return implode( ', ', $partes );
};
echo 'Marcas: ' . $lista( $marchas_todo ) . "\n";
echo 'Tipos: ' . $lista( $tipos_todo ) . "\n";

if ( $duplicados ) {
	echo "\nDuplicados omitidos (" . count( $duplicados ) . "):\n - " . implode( "\n - ", $duplicados ) . "\n";
}
if ( $sin_precio ) {
	echo "\nSin precio (" . count( $sin_precio ) . "):\n - " . implode( "\n - ", $sin_precio ) . "\n";
}
if ( $faltantes ) {
	echo "\nImagen no encontrada (" . count( $faltantes ) . "):\n - " . implode( "\n - ", $faltantes ) . "\n";
}
