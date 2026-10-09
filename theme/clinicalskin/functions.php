<?php
/**
 * Clinical Skin — tema puente.
 *
 * Sirve el sitio estático original como páginas de WordPress:
 *  - lee el HTML de /static/<slug>.html (montaje de C:\xampp\htdocs\clinicalskin)
 *  - imprime <head>, <main>, <footer> y <script> del archivo original
 *  - los enlaces .html se resuelven vía rewrite hacia la página de WP
 *  - WooCommerce usa sus propias páginas (shop, cart, checkout, my-account)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* Métodos de pago del diseño: Webpay (tarjeta), Khipu y Mercado Pago. */
require_once get_theme_file_path( 'pagos.php' );
require_once get_theme_file_path( 'envios.php' );

/** Slugs que maneja WooCommerce: nunca usar el HTML estático. */
function cs_es_woo( $slug ) {
	return in_array( $slug, array( 'shop', 'cart', 'checkout', 'my-account' ), true );
}

/** Slug de la página actual ('inicio' en la portada). */
function cs_slug() {
	global $post;
	if ( is_front_page() ) {
		return 'inicio';
	}
	if ( $post && 'page' === $post->post_type ) {
		return $post->post_name;
	}
	return '';
}

/**
 * Trozos del HTML estático correspondiente a la página actual.
 * Devuelve: titulo, head (meta/link extra), main, footer y scripts.
 */
function cs_piezas() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	/* Valores por defecto: páginas de WooCommerce o sin HTML estático. */
	$cache = array(
		'titulo'  => '',
		'head'    => '',
		'main'    => '',
		'footer'  => '<footer class="site-footer" data-site-footer="simple"></footer>',
		'scripts' => array( 'footer.js', 'main.js' ),
	);

	$slug = cs_slug();
	if ( ! $slug || cs_es_woo( $slug ) ) {
		return $cache;
	}

	$mapa    = array( 'inicio' => 'index' ); // slug => archivo
	$archivo = isset( $mapa[ $slug ] ) ? $mapa[ $slug ] : $slug;
	$ruta    = ABSPATH . 'static/' . $archivo . '.html'; // montaje del sitio estático
	if ( ! is_readable( $ruta ) ) {
		return $cache;
	}

	$html = file_get_contents( $ruta );
	if ( ! $html ) {
		return $cache;
	}

	/* <title> */
	if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $m ) ) {
		$cache['titulo'] = trim( $m[1] );
		$html            = str_replace( $m[0], '', $html );
	}

	/* resto de <head>: meta description, og, favicon, css extra */
	if ( preg_match( '/<head\b[^>]*>(.*?)<\/head>/is', $html, $m ) ) {
		$head = preg_replace( '/<meta\s+charset[^>]*>/i', '', $m[1] );
		$head = preg_replace( '/<meta\s+name="viewport"[^>]*>/i', '', $head );
		/* style.css lo imprime header.php en todas las páginas */
		$head            = preg_replace( '/<link\s+rel="stylesheet"\s+href="css\/style\.css"\s*>\s*/i', '', $head );
		$cache['head']   = trim( $head );
	}

	/* <main> ... </main> (con los href="#" convertidos en anclas inertes,
	   porque con <base> navegarían a la portada) */
	if ( preg_match( '/<main\b[^>]*>.*?<\/main>/is', $html, $m ) ) {
		$cache['main'] = str_replace( 'href="#"', 'href="javascript:void(0)"', $m[0] );
	}

	/* <footer ...></footer> (indica a footer.js si es la versión simple) */
	if ( preg_match( '/<footer\b[^>]*><\/footer>/', $html, $m ) ) {
		$cache['footer'] = $m[0];
	}

	/* scripts del final del body (header.js lo imprime header.php) */
	if ( preg_match_all( '/<script\s+src="js\/([^"]+)"\s*><\/script>/', $html, $m ) ) {
		$scripts = array();
		foreach ( $m[1] as $js ) {
			if ( 'header.js' === $js ) {
				continue;
			}
			$scripts[] = $js;
		}
		if ( $scripts ) {
			$cache['scripts'] = $scripts;
		}
	}

	return $cache;
}

/* Los enlaces del sitio estático apuntan a archivo.html → página de WP.
   WP sustituye $matches[N] (no $1, que es de mod_rewrite). */
add_action(
	'init',
	function () {
		add_rewrite_rule( '^([^/]+)\.html$', 'index.php?pagename=$matches[1]', 'top' );
	}
);

/* Mandamos nosotros con /css/style.css: quitamos los estilos globales del tema. */
add_action(
	'wp_enqueue_scripts',
	function () {
		foreach ( array( 'global-styles', 'classic-theme-styles', 'wp-block-library', 'wp-block-library-inline-css' ) as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	},
	999
);

/* ==================== Tienda con el diseño de productos.html ==================== */

/** ¿Estamos en la tienda o en un listado de productos (categoría, etiqueta, atributo)? */
function cs_es_catalogo() {
	return function_exists( 'is_shop' ) && ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() );
}

/* Panel lateral de filtros: mismo sitio que el aside de productos.html. */
add_action(
	'widgets_init',
	function () {
		register_sidebar(
			array(
				'id'            => 'catalogo-filtros',
				'name'          => 'Catálogo (filtros)',
				'description'   => 'Filtros del panel lateral de la tienda.',
				'before_widget' => '<div class="filter-group">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widgettitle">',
				'after_title'   => '</h2>',
			)
		);
	}
);

/** Abre la página de catálogo: cabecera, miga de pan y panel de filtros. */
function cs_catalogo_abrir() {
	$en_tienda = function_exists( 'is_shop' ) && is_shop();
	$titulo    = $en_tienda ? 'Productos para cada tipo de piel' : single_term_title( '', false );
	$pan       = '<div class="breadcrumb"><a href="' . esc_url( home_url( '/' ) ) . '">Inicio</a> / <a href="' . esc_url( home_url( '/shop/' ) ) . '">Productos</a>';
	if ( ! $en_tienda && $titulo ) {
		$pan .= ' / ' . esc_html( $titulo );
	}
	$pan .= '</div>';
	?>
	<header class="page-header">
		<span class="eyebrow">Catálogo</span>
		<h1><?php echo esc_html( $titulo ); ?></h1>
		<p>Filtra por tipo de piel o preocupación, o mejor aún, deja que el diagnóstico elija por ti.</p>
	</header>
	<div class="tienda-catalogo">
		<?php echo $pan; // la miga de pan ya viene escapada arriba ?>
		<div class="catalog-layout">
			<div class="catalog-main">
	<?php
}

/**
 * Cierra el listado y agrega el panel de filtros.
 *
 * El panel va DESPUÉS del loop a propósito: como el tema no declara soporte
 * de WooCommerce, la consulta de productos se arma recién al imprimir el
 * loop, y el widget de precio se salta el panel si esa consulta todavía no
 * existe. La posición visual la fija css/woo.css (columna 1 en el grid y
 * order: -1 en móvil).
 */
function cs_catalogo_cerrar() {
	echo '</div>';
	echo '<aside class="filters-panel">';
	if ( is_active_sidebar( 'catalogo-filtros' ) ) {
		ob_start();
		dynamic_sidebar( 'catalogo-filtros' );
		echo cs_filtros_ayuda_precio( ob_get_clean() );
	}
	echo '</aside>';
	echo '</div></div>';
}

/**
 * Leyenda del filtro de precio.
 *
 * El widget de WooCommerce muestra el deslizador sin explicar cómo usarlo y
 * mete el botón "Filtrar" antes de la etiqueta de rango (el CSS del tema lo
 * reordena). Aquí se agrega un texto que dice qué hacer.
 */
function cs_filtros_ayuda_precio( $html ) {
	$ayuda     = '<p class="filter-hint">Arrastra los puntos para elegir un rango de precios y, cuando termines, pulsa <strong>Filtrar</strong>.</p>';
	$contador  = 0;
	$nuevo     = str_replace( '<div class="price_slider_wrapper">', '<div class="price_slider_wrapper">' . $ayuda, $html, $contador );
	return $contador ? $nuevo : $html;
}

/* La tarjeta del listado copia la de productos.html: foto, etiquetas,
   título, descripción y pie con precio + botón fantasma "Ver". */
if ( function_exists( 'is_shop' ) ) {
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	add_action( 'woocommerce_before_shop_loop_item_title', 'cs_tienda_etiquetas', 11 );
	add_action( 'woocommerce_after_shop_loop_item_title', 'cs_tienda_descripcion', 8 );
	add_action( 'woocommerce_after_shop_loop_item', 'cs_tienda_pie', 10 );
}

/**
 * Primer valor de un atributo/taxonomía de un producto.
 *
 * Sin $producto_id usa la del loop (get_the_ID()); con $producto_id sirve
 * para el carrito, donde el "post" actual es la página del carrito.
 */
function cs_primer_termino( $taxonomia, $producto_id = 0 ) {
	$producto_id = $producto_id ? $producto_id : get_the_ID();
	$terminos    = wp_get_object_terms( $producto_id, $taxonomia, array( 'fields' => 'names' ) );
	return ( is_wp_error( $terminos ) || ! $terminos ) ? '' : $terminos[0];
}

/** Etiquetas de la tarjeta: tipo de piel + preocupación (como en el diseño). */
function cs_tienda_etiquetas( $producto_id = 0 ) {
	$etiquetas = array();
	$piel      = cs_primer_termino( 'pa_tipo-de-piel', $producto_id );
	if ( $piel ) {
		$etiquetas[] = array( 'Todo' === $piel ? 'Todo tipo' : 'Piel ' . strtolower( $piel ), 'badge badge-accent' );
	}
	$preocupacion = cs_primer_termino( 'pa_preocupacion', $producto_id );
	if ( ! $preocupacion ) {
		$preocupacion = cs_primer_termino( 'pa_paso', $producto_id );
	}
	if ( $preocupacion ) {
		$etiquetas[] = array( $preocupacion, 'badge' );
	}
	if ( ! $etiquetas ) {
		return;
	}
	echo '<div class="product-tags">';
	foreach ( $etiquetas as $etiqueta ) {
		printf( '<span class="%s">%s</span>', esc_attr( $etiqueta[1] ), esc_html( $etiqueta[0] ) );
	}
	echo '</div>';
}

/** Entradilla del producto (la que trae el CSV del catálogo). */
function cs_tienda_descripcion() {
	$producto = wc_get_product( get_the_ID() );
	$texto    = $producto ? $producto->get_short_description() : '';
	if ( '' === trim( $texto ) ) {
		return;
	}
	echo '<p class="desc">' . wp_kses_post( $texto ) . '</p>';
}

/** Pie de la tarjeta: precio a la izquierda y botón fantasma "Ver". */
function cs_tienda_pie() {
	$producto = wc_get_product( get_the_ID() );
	if ( ! $producto ) {
		return;
	}
	echo '<div class="product-footer">';
	echo '<span class="price">' . $producto->get_price_html() . '</span>';
	printf(
		'<a href="%s" class="btn btn-ghost btn-sm">%s</a>',
		esc_url( $producto->get_permalink() ),
		esc_html__( 'Ver', 'clinicalskin' )
	);
	echo '</div>';
}

/* ==================== Carrito con el diseño de carrito.html ==================== */

/** Número de artículos del carrito (para el eyebrow de la cabecera). */
function cs_contador_carrito() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}
	return (int) WC()->cart->get_cart_contents_count();
}

/** Línea de datos de un producto del carrito: categoría · preocupación. */
function cs_carrito_meta( $producto_id ) {
	$partes = array();
	$cats   = wp_get_post_terms( $producto_id, 'product_cat', array( 'fields' => 'names' ) );
	if ( ! is_wp_error( $cats ) && $cats ) {
		$partes[] = $cats[0];
	}
	$preocupacion = cs_primer_termino( 'pa_preocupacion', $producto_id );
	if ( ! $preocupacion ) {
		$preocupacion = cs_primer_termino( 'pa_paso', $producto_id );
	}
	if ( $preocupacion ) {
		$partes[] = $preocupacion;
	}
	return implode( ' · ', $partes );
}

/* El diseño no muestra "también te puede gustar" en la caja de resumen. */
if ( function_exists( 'is_cart' ) ) {
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

	/*
	 * El mensaje nativo de "carrito vacío" es un .woocommerce-info: el JS de
	 * WooCommerce lo toma como un aviso más y lo vuelca sobre la caja de
	 * avisos, duplicándolo. Se sustituye por un ancla invisible en
	 * woocommerce/cart/cart-empty.php, que el mismo JS usa como referencia.
	 */
	remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );
}

/*
 * productos.html sigue en el sitio estático, pero su equivalente en
 * WooCommerce es /shop/: cualquier enlace o URL antigua se redirige (301).
 */
add_action( 'template_redirect', 'cs_redirigir_productos' );
function cs_redirigir_productos() {
	$ruta = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '';
	if ( $ruta && preg_match( '#^/productos\.html/?$#', $ruta ) ) {
		wp_safe_redirect( home_url( '/shop/' ), 301 );
		exit;
	}
}

/*
 * El diagnóstico (resultado.html) enlaza sus productos con las rutas del sitio
 * estático: producto.html?id=<sku> y carrito.html. Ambas se traducen a
 * WooCommerce para que el recorrido siga el sistema de ventas (los id del
 * catálogo del diagnóstico son los SKU de los productos).
 * Prioridad 5: antes que redirect_canonical (10), para que no añada una
 * redirección intermedia.
 */
add_action( 'template_redirect', 'cs_redirigir_diagnostico', 5 );
function cs_redirigir_diagnostico() {
	$ruta = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '';
	if ( ! $ruta ) {
		return;
	}

	/* El logo y los enlaces del menú apuntan a index.html: aquí no existe. */
	if ( preg_match( '#^/index\.html/?$#', $ruta ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}

	/* carrito.html → carrito de WooCommerce (el estático usa localStorage). */
	if ( preg_match( '#^/carrito\.html/?$#', $ruta ) ) {
		wp_safe_redirect( wc_get_cart_url(), 301 );
		exit;
	}

	/* Nombre antiguo del diagnóstico: diagnostico-v2.html → diagnostico.html. */
	if ( preg_match( '#^/diagnostico-v2\.html/?$#', $ruta ) ) {
		wp_safe_redirect( home_url( '/diagnostico.html' ), 301 );
		exit;
	}

	/* producto.html?id=<sku> → ficha del producto; sin id válido → catálogo. */
	if ( preg_match( '#^/producto\.html/?$#', $ruta ) ) {
		$sku         = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';
		$producto_id = 0;
		if ( $sku && function_exists( 'wc_get_product_id_by_sku' ) ) {
			$producto_id = (int) wc_get_product_id_by_sku( $sku );
		}
		if ( $producto_id && 'publish' !== get_post_status( $producto_id ) ) {
			$producto_id = 0;
		}
		wp_safe_redirect( $producto_id ? get_permalink( $producto_id ) : wc_get_page_permalink( 'shop' ), 301 );
		exit;
	}
}

/* Botones ± y refresco automático de totales en la página del carrito. */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( function_exists( 'is_cart' ) && is_cart() ) {
			wp_enqueue_script(
				'cs-carrito',
				get_theme_file_uri( 'js/carrito.js' ),
				array(),
				'1.0.0',
				true
			);
		}
		if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
			wp_enqueue_script(
				'cs-pago',
				get_theme_file_uri( 'js/pago.js' ),
				array( 'jquery' ),
				'1.0.0',
				true
			);
			/* El JS de localización de direcciones vuelve a escribir las
			   etiquetas con los textos genéricos ("Población",
			   "Apartamento…") y el diseño ya trae las suyas. Se quita de las
			   dependencias registradas para no provocar el aviso de WP_Scripts. */
			global $wp_scripts;
			if ( isset( $wp_scripts->registered ) ) {
				foreach ( $wp_scripts->registered as $registro ) {
					$registro->deps = array_diff( (array) $registro->deps, array( 'wc-address-i18n' ) );
				}
			}
			wp_dequeue_script( 'wc-address-i18n' );
			wp_deregister_script( 'wc-address-i18n' );
		}
	}
);

/*
 * "Añadir rutina completa" (resultado.html) apuntaba al carrito estático
 * (localStorage + carrito.html): aquí se reutiliza ese botón para meter los
 * productos en la bolsa de WooCommerce. El mapa nombre → id sale del catálogo
 * real para no duplicar datos.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return;
		}
		$archivo = 'js/puente-diagnostico.js';
		wp_enqueue_script(
			'cs-puente-diagnostico',
			get_theme_file_uri( $archivo ),
			array(),
			(int) filemtime( get_theme_file_path( $archivo ) ),
			true
		);
		$productos = array();
		foreach ( wc_get_products( array( 'limit' => -1, 'return' => 'objects' ) ) as $producto ) {
			$productos[ $producto->get_name() ] = $producto->get_id();
		}
		wp_add_inline_script(
			'cs-puente-diagnostico',
			'window.__csDiagnosticoWoo = ' . wp_json_encode(
				array(
					'productos' => $productos,
					'carrito'   => wc_get_cart_url(),
				)
			) . ';',
			'before'
		);
	}
);

/* ==================== Checkout con el formato de checkout.html ==================== */

if ( function_exists( 'is_checkout' ) ) {
	/*
	 * La tabla de resumen va en la columna derecha y los métodos de pago en
	 * la tarjeta "Pago" de la izquierda: el bloque de pago se desengancha
	 * para poder imprimirlo dentro de esa tarjeta (sigue siendo el mismo
	 * div.woocommerce-checkout-payment que sustituye el JS al actualizar).
	 */
	remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

	/* El checkout.html no muestra el formulario de cupón: se aplica en el carrito. */
	remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
}

/** Botón de pago: como en el diseño, empieza por "Pagar". */
add_filter(
	'woocommerce_order_button_text',
	function () {
		return 'Pagar';
	}
);

/** Fila de producto (.os-item) del resumen de la columna derecha. */
function cs_resumen_pedido() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( empty( $item['data'] ) ) {
			continue;
		}
		$producto = $item['data'];
		?>
		<div class="os-item">
			<div class="os-thumb"><?php echo wp_kses_post( $producto->get_image() ); ?></div>
			<div class="os-copy">
				<strong><?php echo esc_html( $producto->get_name() ); ?></strong>
				<span>Cant. <?php echo (int) $item['quantity']; ?></span>
			</div>
			<span class="os-price"><?php echo wp_kses_post( wc_price( $item['line_subtotal'] ) ); ?></span>
		</div>
		<?php
	}
}

/** Campos de facturación con las etiquetas y el orden del diseño. */
add_filter( 'woocommerce_checkout_fields', 'cs_campos_checkout' );
function cs_campos_checkout( $campos ) {
	if ( empty( $campos['billing'] ) ) {
		return $campos;
	}
	$billing = $campos['billing'];

	/* El diseño no pide empresa ni código postal. */
	unset( $billing['billing_company'] );
	unset( $billing['billing_postcode'] );

	$ajustes = array(
		'billing_country'    => array( 'label' => 'País', 'class' => array( 'form-row', 'field', 'full', 'cs-campo-oculto' ), 'priority' => 5 ),
		'billing_email'      => array( 'label' => 'Correo electrónico', 'placeholder' => 'tu@correo.com', 'class' => array( 'form-row', 'field', 'full' ), 'priority' => 10 ),
		'billing_first_name' => array( 'label' => 'Nombre', 'placeholder' => 'Tu nombre', 'class' => array( 'form-row', 'field' ), 'priority' => 20 ),
		'billing_last_name'  => array( 'label' => 'Apellido', 'placeholder' => 'Tu apellido', 'class' => array( 'form-row', 'field' ), 'priority' => 30 ),
		'billing_phone'      => array( 'label' => 'Teléfono', 'placeholder' => '+56 9 1234 5678', 'required' => true, 'class' => array( 'form-row', 'field', 'full' ), 'priority' => 40 ),
		'billing_state'      => array( 'label' => 'Región', 'class' => array( 'form-row', 'field', 'address-field' ), 'priority' => 50 ),
		'billing_city'       => array( 'label' => 'Comuna', 'class' => array( 'form-row', 'field', 'address-field' ), 'priority' => 60 ),
		'billing_address_1'  => array( 'label' => 'Dirección', 'placeholder' => 'Av. Providencia 1234', 'class' => array( 'form-row', 'field', 'full', 'address-field' ), 'priority' => 70 ),
		'billing_address_2'  => array( 'label' => 'Número y depto. / oficina', 'placeholder' => '502', 'required' => false, 'class' => array( 'form-row', 'field', 'full', 'address-field' ), 'priority' => 80 ),
	);

	foreach ( $ajustes as $clave => $datos ) {
		if ( ! isset( $billing[ $clave ] ) ) {
			continue;
		}
		foreach ( $datos as $propiedad => $valor ) {
			$billing[ $clave ][ $propiedad ] = $valor;
		}
	}

	uasort(
		$billing,
		function ( $a, $b ) {
			$pa = isset( $a['priority'] ) ? (int) $a['priority'] : 100;
			$pb = isset( $b['priority'] ) ? (int) $b['priority'] : 100;
			return $pa - $pb;
		}
	);

	$campos['billing'] = $billing;

	/* "Notas adicionales" es la referencia de entrega del diseño
	   (WooCommerce agrega solo el sufijo "(opcional)"). */
	if ( isset( $campos['order']['order_comments'] ) ) {
		$campos['order']['order_comments']['label']       = 'Referencia';
		$campos['order']['order_comments']['placeholder'] = 'Torre B, junto al parque';
		$campos['order']['order_comments']['class']       = array( 'form-row', 'field', 'full', 'notes' );
	}

	return $campos;
}
