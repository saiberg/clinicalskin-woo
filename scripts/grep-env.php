<?php
/** Busca quién imprime <div class="woocommerce"> en todo el WordPress. grep-env.php */
$raices = array( '/var/www/html/wp-content', '/var/www/html/wp-includes' );
$excluir = array( '/uploads/', '/languages/', '/node_modules/' );
$buscado = 'class="woocommerce"';
$n       = 0;

foreach ( $raices as $raiz ) {
	if ( ! is_dir( $raiz ) ) {
		continue;
	}
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $raiz, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $it as $f ) {
		if ( ! in_array( $f->getExtension(), array( 'php', 'js' ), true ) ) {
			continue;
		}
		$ruta = $f->getPathname();
		if ( preg_match( '#' . preg_quote( implode( '|', $excluir ), '#' ) . '#', $ruta ) ) {
			continue;
		}
		$lineas = @file( $ruta );
		if ( ! $lineas ) {
			continue;
		}
		foreach ( $lineas as $i => $l ) {
			if ( false !== strpos( $l, $buscado ) ) {
				echo $ruta . ':' . ( $i + 1 ) . ': ' . trim( $l ) . "\n";
				$n++;
			}
		}
	}
}
echo "total: $n\n";
