<?php
/** Ver el contenido de las páginas nativas de WooCommerce. wp eval-file /tmp/cs/ver-paginas.php --user=1 */
foreach ( array( 5, 6, 7, 8 ) as $i ) {
	$p = get_post( $i );
	if ( $p ) {
		echo '### ' . $i . ' ' . $p->post_name . ' [' . $p->post_status . ']' . "\n";
		echo $p->post_content . "\n";
	}
}
