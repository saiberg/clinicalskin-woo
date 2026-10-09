<?php
/**
 * Comprueba que el idioma del sitio y las traducciones de WooCommerce cargan.
 * Uso: wp eval-file /tmp/cs/debug-idioma.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo 'locale   : ' . determine_locale() . "\n";
echo 'WPLANG   : ' . get_option( 'WPLANG' ) . "\n";
echo 'core     : ' . __( 'Save changes' ) . "\n";
echo 'woo      : ' . __( 'Cart totals', 'woocommerce' ) . "\n";
echo 'woo otro : ' . __( 'Proceed to checkout', 'woocommerce' ) . "\n";
echo 'mo woo   : ' . ( file_exists( WP_LANG_DIR . '/plugins/woocommerce-es_ES.mo' ) ? 'si' : 'no' ) . "\n";
echo 'mo core  : ' . ( file_exists( WP_LANG_DIR . '/es_ES.mo' ) ? 'si' : 'no' ) . "\n";
echo 'texto    : ' . wp_get_theme()->get( 'Name' ) . ' | ' . WC()->version . "\n";
