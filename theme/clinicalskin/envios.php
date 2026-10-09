<?php
/**
 * Métodos de envío con el formato de checkout.html del diseño estático:
 * Starken (tarifa en vivo desde la API de Starken Pro), BlueExpress
 * ($4.990) y retiro en Santiago (gratis).
 *
 * El selector no va en el resumen de la derecha: se imprime en la tarjeta
 * "Dirección de envío" y WooCommerce lo refresca con un fragmento del AJAX
 * de checkout (woocommerce_update_order_review_fragments), así que el HTML
 * es el mismo en la carga inicial y después de cada cálculo.
 *
 * @package ClinicalSkin
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * Texto secundario por método (visible en el checkout y editable en
 * WooCommerce > Envíos > método > Configurar).
 * ------------------------------------------------------------------ */
foreach ( array( 'flat_rate', 'local_pickup' ) as $cs_metodo_envio ) {
	add_filter( 'woocommerce_instance_form_fields_' . $cs_metodo_envio, 'cs_envio_campo_descripcion' );
}
function cs_envio_campo_descripcion( $campos ) {
	$campos['descripcion'] = array(
		'title'       => 'Texto secundario',
		'type'        => 'text',
		'description' => 'Se muestra bajo el nombre del método en el checkout (diseño).',
		'default'     => '',
		'placeholder' => '2 a 3 días hábiles',
	);
	return $campos;
}

/** Descripción guardada en una instancia concreta de un método de envío. */
function cs_envio_descripcion( $metodo_id, $instancia ) {
	static $cache = array();

	if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
		return '';
	}

	/* Método sin instancia (Starken llega con instance_id 0 porque el plugin
	   guarda todo en su opción global): el texto va en esa opción global. */
	if ( ! $instancia ) {
		if ( ! array_key_exists( $metodo_id . ':global', $cache ) ) {
			$global                = (array) get_option( 'woocommerce_' . $metodo_id . '_settings', array() );
			$cache[ $metodo_id . ':global' ] = isset( $global['descripcion'] ) ? (string) $global['descripcion'] : '';
		}
		return $cache[ $metodo_id . ':global' ];
	}

	$clave = $metodo_id . ':' . (int) $instancia;
	if ( array_key_exists( $clave, $cache ) ) {
		return $cache[ $clave ];
	}
	$cache[ $clave ] = '';

	global $wpdb;
	$zona_id = $wpdb->get_var(
		$wpdb->prepare(
			'SELECT zone_id FROM ' . $wpdb->prefix . 'woocommerce_shipping_zone_methods WHERE instance_id = %d',
			(int) $instancia
		)
	);
	if ( ! $zona_id ) {
		return $cache[ $clave ];
	}

	$zona = new WC_Shipping_Zone( (int) $zona_id );
	foreach ( $zona->get_shipping_methods( false ) as $metodo ) {
		if ( $metodo->id === $metodo_id && (int) $metodo->get_instance_id() === (int) $instancia ) {
			$cache[ $clave ] = (string) $metodo->get_instance_option( 'descripcion' );
			break;
		}
	}
	return $cache[ $clave ];
}

/* ---------------------------------------------------------------------
 * Tarjeta "Método de despacho".
 * ------------------------------------------------------------------ */

/** HTML del selector (misma salida para la carga inicial y para el AJAX). */
function cs_metodos_envio_html() {
	ob_start();
	?>
	<div class="cs-metodos-envio" data-cs-envio>
		<h3 class="ship-subhead">Método de despacho</h3>
		<?php
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			echo '<p class="fc-sub">Los métodos de despacho aparecen al iniciar tu pedido.</p>';
		} else {
			$disponible = WC()->cart->needs_shipping() && WC()->cart->show_shipping();
			$paquetes   = $disponible ? WC()->shipping()->get_packages() : array();
			$paquetes   = array_filter( (array) $paquetes );

			if ( ! $paquetes ) {
				echo '<p class="fc-sub">Ingresa tu dirección para ver las opciones de despacho.</p>';
			} else {
				$elegidos = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods', array() ) : array();

				foreach ( $paquetes as $i => $paquete ) {
					$rates = empty( $paquete['rates'] ) ? array() : (array) $paquete['rates'];
					if ( ! $rates ) {
						continue;
					}
					$elegido = isset( $elegidos[ $i ] ) && $elegidos[ $i ] ? $elegidos[ $i ] : '';
					/* Si el método guardado en la sesión ya no se ofrece (p. ej.
					   se quedó una sola tarifa de Chilexpress) se marca el
					   primero disponible para que siempre haya uno seleccionado. */
					if ( $elegido && ! isset( $rates[ $elegido ] ) ) {
						$elegido = (string) array_key_first( $rates );
					}

					echo '<ul class="woocommerce-shipping-methods ship-methods">';
					foreach ( $rates as $clave => $metodo ) {
						$id       = $metodo->get_id();
						$activo   = $elegido ? ( $clave === $elegido ) : false;
						$costo    = (float) $metodo->get_cost();
						$precio   = $costo > 0 ? wp_strip_all_tags( wc_price( $costo ) ) : __( 'Gratis', 'clinicalskin' );
						$metadesc = cs_envio_descripcion( $metodo->get_method_id(), $metodo->get_instance_id() );
						if ( '' === $metadesc ) {
							$meta_rate = $metodo->get_meta_data();
							if ( ! empty( $meta_rate['cs_servicio'] ) ) {
								$metadesc = (string) $meta_rate['cs_servicio'];
							}
						}
						$radio_id = 'shipping_method_' . (int) $i . '_' . sanitize_title( $id );
						?>
						<li class="ship-method">
							<label class="radio-card<?php echo $activo ? ' selected' : ''; ?>" for="<?php echo esc_attr( $radio_id ); ?>">
								<input type="radio" name="shipping_method[<?php echo (int) $i; ?>]" data-index="<?php echo (int) $i; ?>" id="<?php echo esc_attr( $radio_id ); ?>" value="<?php echo esc_attr( $id ); ?>" class="shipping_method"<?php checked( $activo ); ?> />
								<span class="rc-copy">
									<strong><?php echo esc_html( $metodo->get_label() ); ?></strong>
									<?php if ( '' !== $metadesc ) : ?>
										<span><?php echo esc_html( $metadesc ); ?></span>
									<?php endif; ?>
								</span>
								<span class="rc-price"><?php echo esc_html( $precio ); ?></span>
							</label>
						</li>
						<?php
					}
					echo '</ul>';
				}
			}
		}
		?>
	</div>
	<?php
	return ob_get_clean();
}

/* El checkout refresca el selector junto al resto de fragmentos. */
add_filter( 'woocommerce_update_order_review_fragments', 'cs_envio_fragmento' );
function cs_envio_fragmento( $fragmentos ) {
	$fragmentos['[data-cs-envio]'] = cs_metodos_envio_html();
	return $fragmentos;
}

/* El método de Starken no cotiza sin comuna de destino: mientras no llegue
   la de envío usamos la de facturación (el formulario de compra solo trae
   una dirección) y, si tampoco hay, la comuna de origen de la tienda. Así
   la tarjeta del diseño aparece desde el primer momento y se recalcula con
   la dirección real en cuanto el cliente la elige. */
add_filter( 'woocommerce_cart_shipping_packages', 'cs_envio_destino_inicial' );
function cs_envio_destino_inicial( $paquetes ) {
	foreach ( (array) $paquetes as $indice => $paquete ) {
		if ( ! empty( $paquete['destination']['city'] ) ) {
			continue;
		}

		$ciudad = '';
		if ( function_exists( 'WC' ) && WC()->customer ) {
			$ciudad = trim( (string) WC()->customer->get_billing_city() );
		}
		if ( '' === $ciudad ) {
			$ciudad = 'Santiago';
		}
		$paquetes[ $indice ]['destination']['city'] = $ciudad;
	}
	return $paquetes;
}

/* El plugin oficial de Chilexpress sustituye las regiones de Chile por sus
   códigos internos (R1..RM). Starken (Anyda) valida la región con el formato
   original de WooCommerce (CL-RM, CL-AN...), así que se restaura la lista
   nativa: Chilexpress también la acepta porque sus códigos CL-XX están
   habilitados como región en la opción del plugin. */
add_filter( 'woocommerce_states', 'cs_regiones_chile_originales', 99 );
function cs_regiones_chile_originales( $estados ) {
	static $originales = null;
	if ( null === $originales && function_exists( 'WC' ) ) {
		$originales = include WC()->plugin_path() . '/i18n/states.php';
	}
	if ( is_array( $originales ) && ! empty( $originales['CL'] ) ) {
		$estados['CL'] = $originales['CL'];
	}
	return $estados;
}

/* El plugin nombra cada tarifa "Título + servicio" (p. ej. "Starken a domicilio
 * Domicilio Normal"). Cuando solo llega un servicio mostramos únicamente el
 * título, que es como aparece en el diseño; si Starken devuelve varios
 * servicios se conserva el sufijo para poder distinguirlos. */
add_filter( 'woocommerce_package_rates', 'cs_envio_etiqueta_starken', 10, 2 );
function cs_envio_etiqueta_starken( $tasas, $paquete ) {
	$starken = array();
	foreach ( (array) $tasas as $clave => $tasa ) {
		if ( 0 === strpos( (string) $tasa->get_id(), 'wc_andresreyesdev_starken_' ) ) {
			$starken[ $clave ] = $tasa;
		}
	}
	if ( 1 !== count( $starken ) ) {
		return $tasas;
	}

	$ajustes  = (array) get_option( 'woocommerce_wc_andresreyesdev_starken_settings', array() );
	$titulo   = ! empty( $ajustes['title'] ) ? (string) $ajustes['title'] : 'Starken a domicilio';
	$primero  = array_key_first( $starken );
	$etiqueta = trim( (string) $starken[ $primero ]->get_label() );

	if ( $titulo !== $etiqueta && 0 === strpos( $etiqueta, $titulo ) ) {
		$starken[ $primero ]->set_label( $titulo );
	}
	return $tasas;
}

/* Chilexpress puede devolver varios servicios (PREX, CHEX, XTEN, AMPM...).
   En el checkout del diseño va una sola tarjeta: se conserva el más barato,
   con el nombre común "Chilexpress a domicilio" y el servicio como texto
   secundario. Si la API falla (el plugin marca costo -1) no se muestra
   ninguna tarjeta en lugar de un error con precio negativo. */
add_filter( 'woocommerce_package_rates', 'cs_envio_chilexpress_unico', 20, 2 );
function cs_envio_chilexpress_unico( $tasas, $paquete ) {
	$chilexpress = array();
	foreach ( (array) $tasas as $clave => $tasa ) {
		if ( 'chilexpress_woo_oficial' === $tasa->get_method_id() ) {
			$chilexpress[ $clave ] = $tasa;
		}
	}
	if ( ! $chilexpress ) {
		return $tasas;
	}

	$mejor_clave = null;
	$mejor_costo = null;
	foreach ( $chilexpress as $clave => $tasa ) {
		$costo = (float) $tasa->get_cost();
		if ( $costo < 0 ) {
			continue;
		}
		if ( null === $mejor_costo || $costo < $mejor_costo ) {
			$mejor_clave = $clave;
			$mejor_costo = $costo;
		}
	}

	foreach ( $chilexpress as $clave => $tasa ) {
		if ( $clave !== $mejor_clave ) {
			unset( $tasas[ $clave ] );
		}
	}
	if ( null === $mejor_clave ) {
		return $tasas;
	}

	$etiqueta = (string) $chilexpress[ $mejor_clave ]->get_label();
	$servicio = trim( (string) preg_replace( '/^Chilexpress\s*-\s*/i', '', $etiqueta ) );

	$chilexpress[ $mejor_clave ]->set_label( 'Chilexpress a domicilio' );
	$chilexpress[ $mejor_clave ]->add_meta_data( 'cs_servicio', cs_servicio_amigable( $servicio ) );
	return $tasas;
}

/** "PREX día sig. AM" -> "Entrega el día siguiente (hasta 13:30)". */
function cs_servicio_amigable( $servicio ) {
	$servicio = trim( (string) $servicio );
	if ( '' === $servicio ) {
		return '';
	}
	if ( false !== stripos( $servicio, 'día sig' ) || false !== stripos( $servicio, 'dia sig' ) ) {
		$texto = 'Entrega el día siguiente';
		if ( preg_match( '/\bAM\b/', $servicio ) ) {
			$texto .= ' (hasta 13:30)';
		} elseif ( preg_match( '/\bPM\b/', $servicio ) ) {
			$texto .= ' (hasta 19:00)';
		}
		return $texto;
	}
	if ( false !== stripos( $servicio, 'sábado' ) || false !== stripos( $servicio, 'sabado' ) ) {
		return 'Entrega el sábado siguiente';
	}
	if ( preg_match( '/hasta\s+(\d+)\s*d/i', $servicio, $coincide ) ) {
		$dias = (int) $coincide[1];
		return 'Hasta ' . $dias . ( 1 === $dias ? ' día hábil' : ' días hábiles' );
	}
	return $servicio;
}

/* ---------------------------------------------------------------------
 * Textos de la página "pedido recibido".
 * ------------------------------------------------------------------ */

/** "Starken a domicilio · 2 a 3 días hábiles" (o retiro si no hay despacho). */
/** Nombre del método elegido ("Starken a domicilio") para la ficha de resumen. */
function cs_despacho_texto( $pedido ) {
	if ( ! $pedido || ! count( $pedido->get_shipping_methods() ) ) {
		return __( 'Retiro en punto de atención', 'clinicalskin' );
	}
	return (string) $pedido->get_shipping_method();
}

/** Rango de entrega ("Jue 3 — Sáb 5 oct") o "Listo en 24 horas" si es retiro. */
function cs_entrega_texto( $pedido ) {
	if ( ! $pedido || ! count( $pedido->get_shipping_methods() ) ) {
		return __( 'Listo en 24 horas', 'clinicalskin' );
	}
	$titulo = mb_strtolower( (string) $pedido->get_shipping_method(), 'UTF-8' );

	if ( false !== strpos( $titulo, 'retiro' ) ) {
		return __( 'Listo en 24 horas', 'clinicalskin' );
	}

	/* Días naturales desde la compra: 1 a 2 para express, 3 a 6 para estándar. */
	$inicio = 3;
	$fin    = 6;
	if ( false !== strpos( $titulo, 'blue' ) ) {
		$inicio = 2;
		$fin    = 3;
	}

	/* Chilexpress: el plazo depende del servicio elegido (guardado en el
	   pedido como texto secundario de la tarifa). */
	if ( false !== strpos( $titulo, 'chilexpress' ) ) {
		$servicio = '';
		foreach ( $pedido->get_items( 'shipping' ) as $item_envio ) {
			$meta = $item_envio->get_meta( 'cs_servicio' );
			if ( $meta ) {
				$servicio = (string) $meta;
				break;
			}
		}
		if ( false !== stripos( $servicio, 'día siguiente' ) ) {
			$inicio = 1;
			$fin    = 2;
		} elseif ( preg_match( '/hasta\s+(\d+)\s*d/i', $servicio, $coincide_cxp ) ) {
			$inicio = (int) $coincide_cxp[1];
			$fin    = $inicio + 1;
		} elseif ( false !== stripos( $servicio, 'sábado' ) ) {
			$inicio = 4;
			$fin    = 5;
		}
	}

	$ahora = time();
	return cs_fecha_corta( $ahora + ( $inicio * DAY_IN_SECONDS ), false ) . ' — ' . cs_fecha_corta( $ahora + ( $fin * DAY_IN_SECONDS ), true );
}

/** "Jue 3" / "Sáb 5 oct" en español, sin depender de la configuración de PHP. */
function cs_fecha_corta( $timestamp, $con_mes ) {
	$dias   = array( 'Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb' );
	$meses  = array( 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic' );
	$texto  = $dias[ (int) gmdate( 'w', $timestamp ) ] . ' ' . (int) gmdate( 'j', $timestamp );
	if ( $con_mes ) {
		$texto .= ' ' . $meses[ (int) gmdate( 'n', $timestamp ) - 1 ];
	}
	return $texto;
}
