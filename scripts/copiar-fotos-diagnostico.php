<?php
/**
 * Copia la foto de producto de cada elegido del diagnostico a
 * static/img/productos/diag-*.jpg (max 700px, jpg q82), que es donde
 * resultado.html las pide (lado estatico y lado Woo usan /img montado).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$mapa = array(
	206 => 'diag-dermopure-ultra.jpg',
	262 => 'diag-agua-termal-300.jpg',
	200 => 'diag-dermopure-noche.jpg',
	254 => 'diag-mineral89.jpg',
	256 => 'diag-liftactiv.jpg',
	216 => 'diag-elasticity3d.jpg',
	186 => 'diag-crema-noche.jpg',
	222 => 'diag-repuesto-dia.jpg',
	212 => 'diag-hydro-fluid.jpg',
	268 => 'diag-effaclar.jpg',
	192 => 'diag-aquaphor.jpg',
);

$destino_dir = ABSPATH . 'img/productos';
if ( ! is_dir( $destino_dir ) ) {
	mkdir( $destino_dir, 0755, true );
}

$ok = 0;
$fallos = array();
foreach ( $mapa as $producto_id => $archivo ) {
	$thumb = (int) get_post_thumbnail_id( $producto_id );
	$ruta  = $thumb ? get_attached_file( $thumb ) : '';
	if ( ! $ruta || ! file_exists( $ruta ) ) {
		$fallos[] = $producto_id . ' (sin adjunto)';
		continue;
	}
	$ed = wp_get_image_editor( $ruta );
	if ( is_wp_error( $ed ) ) {
		$fallos[] = $producto_id . ' (' . $ed->get_error_message() . ')';
		continue;
	}
	$ed->resize( 700, 700, false );
	$guardado = $ed->save( $destino_dir . '/' . $archivo );
	if ( is_wp_error( $guardado ) ) {
		$fallos[] = $producto_id . ' (' . $guardado->get_error_message() . ')';
		continue;
	}
	$ok++;
	printf( "%s <- %s (%d bytes)\n", $archivo, basename( $ruta ), (int) filesize( $guardado['path'] ) );
}

printf( "\nCopiadas: %d | fallos: %d %s\n", $ok, count( $fallos ), $fallos ? '-> ' . implode( ' / ', $fallos ) : '' );
