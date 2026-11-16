<?php
if ( ! defined( 'ABSPATH' ) ) exit;
echo "=== OPcache status ===\n";
if ( function_exists('opcache_get_status') ) {
  $s = @opcache_get_status(false);
  if ( $s ) {
    echo "  enabled: SI\n";
    echo "  validate_timestamps: " . ini_get('opcache.validate_timestamps') . "\n";
    echo "  revalidate_freq: " . ini_get('opcache.revalidate_freq') . "\n";
  } else { echo "  enabled: NO (o no accesible)\n"; }
} else { echo "  funcion no disponible\n"; }
echo "\n=== ini relevantes ===\n";
foreach ( array('opcache.enable','opcache.validate_timestamps','opcache.revalidate_freq','opcache.enable_cli') as $k ) {
  echo "  $k = " . var_export( ini_get($k), true ) . "\n";
}
echo "\n=== Server software ===\n";
echo '  ' . ( $_SERVER['SERVER_SOFTWARE'] ?? '?' ) . "\n";
echo "\n=== Plugins activos ===\n";
foreach ( get_option('active_plugins', array()) as $p ) echo "  $p\n";
