<?php
/**
 * Cabecera de todas las páginas: replica el <head> y el arranque del body
 * del sitio estático, sumando wp_head() para WooCommerce/plugins.
 */
$p = cs_piezas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<base href="<?php echo esc_url( home_url( '/' ) ); ?>">
<title><?php echo $p['titulo'] ? $p['titulo'] : esc_html( wp_get_document_title() ); ?></title>
<?php wp_head(); ?>
<?php $ver_css_diseno = @filemtime( ABSPATH . 'css/style.css' ) ?: (int) filemtime( __FILE__ ); ?>
<link rel="stylesheet" href="css/style.css?ver=<?php echo (int) $ver_css_diseno; ?>">
<link rel="stylesheet" href="<?php echo esc_url( add_query_arg( 'ver', (int) filemtime( get_theme_file_path( 'css/woo.css' ) ), get_theme_file_uri( 'css/woo.css' ) ) ); ?>">
<?php if ( $p['head'] ) : ?>
<?php echo $p['head']; ?>
<?php endif; ?>
</head>
<body>
<?php wp_body_open(); ?>

<header class="site-header" data-site-header></header>
<?php
/* header.js vive en el montaje del sitio estático; la consulta de versión
   cambia con cada edición para que el navegador no sirva una copia vieja. */
$ver_header_js = @filemtime( ABSPATH . 'js/header.js' ) ?: (int) filemtime( __FILE__ );
?>
<script src="js/header.js?ver=<?php echo (int) $ver_header_js; ?>"></script>
<?php if ( function_exists( 'WC' ) && WC()->cart && function_exists( 'cs_contador_carrito' ) ) : ?>
<script>
/**
 * La bolsa del menú es parte del header.js del sitio estático y apunta a
 * carrito.html; aquí se reapunta al carrito de WooCommerce y se le pone
 * contador. carrito.js vuelve a llamar a __csBolsa al vaciar el carrito.
 */
(function () {
	var url = <?php echo wp_json_encode( wc_get_cart_url() ); ?>;

	/* Todo se escribe solo si cambia: en el carrito hay un MutationObserver
	   que vuelve a llamar a esta función y una escritura inútil sería un
	   bucle infinito. */
	window.__csBolsa = function (n) {
		var bolsa = document.querySelector('a.nav-cart');
		if (!bolsa) {
			return;
		}
		var href = String(url);
		if (bolsa.getAttribute('href') !== href) {
			bolsa.setAttribute('href', href);
		}
		var rotulo = n
			? 'Ver carrito (' + n + (1 === n ? ' producto)' : ' productos)')
			: 'Ver carrito';
		if (bolsa.getAttribute('aria-label') !== rotulo) {
			bolsa.setAttribute('aria-label', rotulo);
		}
		var badge = bolsa.querySelector('.nav-cart-count');
		if (!badge) {
			badge = document.createElement('span');
			badge.className = 'nav-cart-count';
			bolsa.appendChild(badge);
		}
		var texto = n > 9 ? '9+' : String(n);
		if (badge.textContent !== texto) {
			badge.textContent = texto;
		}
		var visible = n ? '' : 'none';
		if (badge.style.display !== visible) {
			badge.style.display = visible;
		}
	};

	window.__csBolsa(<?php echo (int) cs_contador_carrito(); ?>);
})();
</script>
<?php endif; ?>
