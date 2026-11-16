<?php
if ( ! defined( 'ABSPATH' ) ) exit;
echo "=== Plugins de cache ===";
foreach ( get_option('active_plugins', array()) as $p ) {
  if ( preg_match('/cache|litespeed|rocket|optimize|speed/i', $p) ) echo "  ACTIVO: $p\n";
}
echo "=== OPcache ===\n";
echo '  enabled: ' . ( function_exists('opcache_get_status') && @opcache_get_status(false) ? 'SI' : 'NO' ) . "\n";
echo "=== Object cache externo ===\n";
echo '  wp_using_ext_object_cache: ' . ( wp_using_ext_object_cache() ? 'SI' : 'NO' ) . "\n";
echo "=== Archivo static en hosting ===\n";
$ruta = ABSPATH . 'static/diagnostico.html';
echo '  ruta: ' . $ruta . "\n";
echo '  existe: ' . ( is_readable($ruta) ? 'SI' : 'NO' ) . "\n";
if ( is_readable($ruta) ) {
  $c = file_get_contents($ruta);
  echo '  mtime: ' . date('Y-m-d H:i:s', filemtime($ruta)) . "\n";
  echo '  contiene rostro-zonas: ' . ( strpos($c,'rostro-zonas') !== false ? 'SI' : 'NO' ) . "\n";
  echo '  contiene face-scan: ' . ( strpos($c,'face-scan') !== false ? 'SI' : 'NO' ) . "\n";
}
