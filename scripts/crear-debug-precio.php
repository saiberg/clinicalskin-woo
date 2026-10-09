<?php
/**
 * Crea un mu-plugin temporal que vuelca en el footer por qué el widget
 * de precio no se muestra (a prueba de errores). Borrar con:
 *   wp eval-file /tmp/cs/borrar-debug-precio.php
 * Uso: wp eval-file /tmp/cs/crear-debug-precio.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir = WP_CONTENT_DIR . '/mu-plugins';
if ( ! is_dir( $dir ) ) {
	wp_mkdir_p( $dir );
}

$codigo = <<<'PHP'
<?php
add_action(
	'wp_footer',
	function () {
		if ( ! isset( $_GET['dbg'] ) ) {
			return;
		}
		$partes = array( 'url=' . wp_parse_url( home_url( $_SERVER['REQUEST_URI'] ?? '/' ), PHP_URL_PATH ) );
		try {
			$consulta = WC()->query->get_main_query();
			$partes[] = 'post_count=' . ( $consulta ? (int) $consulta->post_count : 'null' );
		} catch ( Throwable $e ) {
			$partes[] = 'errQ=' . $e->getMessage();
		}
		try {
			$precios = WC()->query->get_filtered_price();
			$partes[] = 'min=' . ( isset( $precios->min_price ) ? $precios->min_price : 'n/a' );
			$partes[] = 'max=' . ( isset( $precios->max_price ) ? $precios->max_price : 'n/a' );
		} catch ( Throwable $e ) {
			$partes[] = 'errP=' . $e->getMessage();
		}
		$partes[] = 'db=' . (string) get_option( 'woocommerce_db_version' );
		$partes[] = 'is_shop=' . ( is_shop() ? 1 : 0 );
		$partes[] = 'is_tax=' . ( is_product_taxonomy() ? 1 : 0 );
		echo '<!-- dbg ' . esc_html( implode( ' ', $partes ) ) . ' -->';
	}
);
PHP;

file_put_contents( $dir . '/cs-debug-precio.php', $codigo );
echo "creado: {$dir}/cs-debug-precio.php\n";
