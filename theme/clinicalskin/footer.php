<?php
/**
 * Pie de página: el mismo <footer> del archivo estático (footer.js decide si
 * es la versión completa o la barra simple) y los scripts del final del body.
 */
$p = cs_piezas();
?>
<?php echo $p['footer']; ?>
<?php
/* Consulta de versión por archivo: sin ella el navegador puede servir una
   copia vieja de main.js/footer.js y los cambios no se ven. */
$cs_scripts = (array) $p['scripts'];
/* logica.js define window.CS_DIAG, el catálogo que usa el buscador del
   header: las páginas de WooCommerce no lo traen en su HTML. */
if ( ! in_array( 'logica.js', $cs_scripts, true ) ) {
	array_unshift( $cs_scripts, 'logica.js' );
}
foreach ( $cs_scripts as $js ) :
	$ruta_js  = ABSPATH . 'js/' . $js;
	$ver_js   = @filemtime( $ruta_js );
	$ver_js   = $ver_js ? $ver_js : (int) filemtime( __FILE__ );
	?>
<script src="js/<?php echo esc_attr( $js ); ?>?ver=<?php echo (int) $ver_js; ?>"></script>
<?php endforeach; ?>
<?php wp_footer(); ?>
</body>
</html>
