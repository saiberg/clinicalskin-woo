<?php
/**
 * Borra el mu-plugin temporal de depuración del widget de precio.
 * Uso: wp eval-file /tmp/cs/borrar-debug-precio.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$archivo = WP_CONTENT_DIR . '/mu-plugins/cs-debug-precio.php';
if ( file_exists( $archivo ) ) {
	unlink( $archivo );
	echo "borrado: {$archivo}\n";
} else {
	echo "no existía: {$archivo}\n";
}
