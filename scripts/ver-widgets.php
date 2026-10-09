<?php
/**
 * Muestra los widgets del panel de filtros del catálogo.
 * Uso: wp eval-file /tmp/cs/ver-widgets.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sw        = get_option( 'sidebars_widgets' );
$sidebar   = isset( $sw['catalogo-filtros'] ) ? (array) $sw['catalogo-filtros'] : array();
echo 'sidebar: ' . implode( ',', $sidebar ) . "\n";
echo 'precio : ' . json_encode( get_option( 'widget_woocommerce_price_filter' ) ) . "\n";
echo 'cats   : ' . json_encode( get_option( 'widget_woocommerce_product_categories' ) ) . "\n";
echo 'nav    : ' . json_encode( get_option( 'widget_woocommerce_layered_nav' ) ) . "\n";
