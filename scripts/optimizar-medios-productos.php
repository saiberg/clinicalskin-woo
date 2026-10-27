<?php
/**
 * Reconvierte las imágenes destacadas de los productos a JPEG optimizado.
 * Mantiene IDs de adjunto, productos, ALT y asociaciones; sustituye los PNG
 * por JPEG calidad 82, máximo 1200 px, y regenera tamaños de WooCommerce.
 *
 * Uso:
 *   docker compose -f C:\docker\clinicalskin-woo\docker-compose.yml run --rm cli wp eval-file /tmp/cs/optimizar-medios-productos.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/image.php';

function cs_opt_bytes_uploads() {
	$base  = wp_upload_dir()['basedir'];
	$total = 0;
	if ( ! is_dir( $base ) ) {
		return 0;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $base, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $it as $file ) {
		if ( $file->isFile() ) {
			$total += $file->getSize();
		}
	}
	return $total;
}

$productos = wc_get_products( array( 'limit' => -1, 'status' => 'publish', 'return' => 'objects' ) );
$ids       = array();
foreach ( $productos as $producto ) {
	$id = (int) $producto->get_image_id();
	if ( $id ) {
		$ids[ $id ] = true;
	}
}

$antes   = cs_opt_bytes_uploads();
$hechas  = 0;
$omitidas = 0;
$errores = array();
printf( "Productos publicados: %d | imágenes destacadas únicas: %d\n", count( $productos ), count( $ids ) );
printf( "Uploads antes: %.1f MB\n", $antes / 1048576 );

foreach ( array_keys( $ids ) as $id ) {
	$old_file = get_attached_file( $id );
	$old_mime = get_post_mime_type( $id );
	$old_meta = wp_get_attachment_metadata( $id );
	if ( ! $old_file || ! file_exists( $old_file ) ) {
		$errores[] = $id . ': no se encontró el archivo original';
		continue;
	}
	if ( 'image/png' !== $old_mime || 'png' !== strtolower( pathinfo( $old_file, PATHINFO_EXTENSION ) ) ) {
		$omitidas++;
		continue;
	}

	$editor = wp_get_image_editor( $old_file );
	if ( is_wp_error( $editor ) ) {
		$errores[] = $id . ': ' . $editor->get_error_message();
		continue;
	}
	$editor->set_quality( 82 );
	$editor->resize( 1200, 1200, false );
	$dir      = dirname( $old_file );
	$filename = pathinfo( $old_file, PATHINFO_FILENAME ) . '.jpg';
	$new_path = trailingslashit( $dir ) . wp_unique_filename( $dir, $filename );
	$saved    = $editor->save( $new_path, 'image/jpeg' );
	if ( is_wp_error( $saved ) || empty( $saved['path'] ) || ! file_exists( $saved['path'] ) ) {
		$errores[] = $id . ': no se pudo guardar el JPEG';
		continue;
	}
	$new_path = $saved['path'];

	$mime_upd = wp_update_post( array( 'ID' => $id, 'post_mime_type' => 'image/jpeg' ), true );
	if ( is_wp_error( $mime_upd ) ) {
		@unlink( $new_path );
		$errores[] = $id . ': ' . $mime_upd->get_error_message();
		continue;
	}
	update_attached_file( $id, $new_path );
	delete_post_meta( $id, '_wp_attachment_metadata' );
	$new_meta = wp_generate_attachment_metadata( $id, $new_path );
	if ( is_wp_error( $new_meta ) || ! is_array( $new_meta ) || empty( $new_meta['file'] ) ) {
		update_attached_file( $id, $old_file );
		wp_update_post( array( 'ID' => $id, 'post_mime_type' => $old_mime ) );
		wp_update_attachment_metadata( $id, $old_meta );
		@unlink( $new_path );
		$errores[] = $id . ': no se pudieron regenerar las miniaturas';
		continue;
	}
	wp_update_attachment_metadata( $id, $new_meta );

	/* Borra fuentes y tamaños antiguos solo después de registrar el JPEG nuevo. */
	$old_files = array( $old_file );
	if ( ! empty( $old_meta['sizes'] ) && is_array( $old_meta['sizes'] ) ) {
		foreach ( $old_meta['sizes'] as $size ) {
			if ( ! empty( $size['file'] ) ) {
				$old_files[] = trailingslashit( dirname( $old_file ) ) . $size['file'];
			}
		}
	}
	if ( ! empty( $old_meta['original_image'] ) ) {
		$old_files[] = trailingslashit( dirname( $old_file ) ) . $old_meta['original_image'];
	}
	foreach ( array_unique( $old_files ) as $old_path ) {
		if ( $old_path !== $new_path && file_exists( $old_path ) ) {
			@unlink( $old_path );
		}
	}

	$hechas++;
	printf( "%d | %s -> %s | %.0f KB\n", $id, basename( $old_file ), basename( $new_path ), filesize( $new_path ) / 1024 );
}

$despues = cs_opt_bytes_uploads();
printf( "\nConvertidas: %d | omitidas: %d | errores: %d\n", $hechas, $omitidas, count( $errores ) );
printf( "Uploads después: %.1f MB | reducción: %.1f MB\n", $despues / 1048576, ( $antes - $despues ) / 1048576 );
if ( $errores ) {
	echo "Errores:\n - " . implode( "\n - ", $errores ) . "\n";
	exit( 1 );
}
