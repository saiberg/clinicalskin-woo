<?php
/**
 * Crea un archivo de prueba temporal en la raiz de WordPress para comprobar
 * como carga las traducciones el contexto web (Apache). Se borra despues.
 * Uso: wp eval-file /tmp/cs/crear-test-idioma.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$codigo = <<<'PHP'
<?php
require __DIR__ . '/wp-load.php';
header( 'Content-Type: text/plain; charset=utf-8' );
echo 'locale=' . determine_locale() . "\n";
echo 'woo=' . __( '%d in cart', 'woocommerce' ) . "\n";
echo 'core=' . __( 'Save changes' ) . "\n";
echo 'woo_loaded=' . ( is_textdomain_loaded( 'woocommerce' ) ? 'si' : 'no' ) . "\n";
echo 'default_loaded=' . ( is_textdomain_loaded( 'default' ) ? 'si' : 'no' ) . "\n";
echo 'wp_lang_dir=' . WP_LANG_DIR . "\n";
echo 'filter=' . apply_filters( 'determine_locale', '' ) . "\n";
PHP;

file_put_contents( ABSPATH . 'cs-idioma.php', $codigo );
echo "creado: " . ABSPATH . "cs-idioma.php\n";
