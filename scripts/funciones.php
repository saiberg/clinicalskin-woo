<?php
/** ¿Está cargada la función nueva y su filtro? ¿Y qué dice opcache? */

echo 'funcion: ' . ( function_exists( 'cs_envio_destino_inicial' ) ? 'si' : 'no' ) . PHP_EOL;
echo 'filtro_shipping_packages: ' . ( has_filter( 'woocommerce_shipping_packages' ) ? 'si' : 'no' ) . PHP_EOL;
echo 'funcion_descripcion: ' . ( function_exists( 'cs_envio_descripcion' ) ? 'si' : 'no' ) . PHP_EOL;
