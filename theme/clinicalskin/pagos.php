<?php
/**
 * Métodos de pago del diseño (checkout.html): tarjeta vía Webpay, Khipu y
 * Mercado Pago.
 *
 * Son gateways propios de WooCommerce: crean el pedido y lo dejan pendiente
 * de pago (la pasarela real todavía no está conectada). Los campos de tarjeta
 * del diseño se imprimen sin atributo name —igual que en checkout.html—, así
 * que no se envían ni se guardan: los datos de la tarjeta nunca llegan a la
 * tienda.
 * Webpay sí está conectado: el plugin oficial de Transbank opera en ambiente
 * de integración (pruebas) y la fila del diseño la usa ese gateway real, con
 * el chip, el título y la nota del diseño (filtros más abajo). La maqueta
 * "webpay" queda desactivada. Khipu y Mercado Pago siguen sin credenciales.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

/** Base común: título + subtítulo del diseño y creación del pedido. */
abstract class CS_Gateway_Pago extends WC_Payment_Gateway {

	/** @var string Texto secundario bajo el nombre del método. */
	protected $subtitulo = '';

	public function __construct( $id, $titulo, $subtitulo, $campos ) {
		$this->id                 = $id;
		$this->method_title       = $titulo;
		$this->method_description = $subtitulo;
		$this->has_fields         = (bool) $campos;
		$this->supports           = array( 'products' );

		$this->title       = $titulo;
		$this->description = ''; /* el texto va en el subtítulo de la tarjeta */
		$this->subtitulo   = $subtitulo;

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Activar/Desactivar', 'clinicalskin' ),
				'type'    => 'checkbox',
				'label'   => __( 'Activar este método de pago', 'clinicalskin' ),
				'default' => 'yes',
			),
		);
		$this->init_settings();

		$this->enabled = $this->get_option( 'enabled', 'yes' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/** Texto secundario (lo imprime woocommerce/checkout/payment-method.php). */
	public function cs_subtitulo() {
		return $this->subtitulo;
	}

	/**
	 * El pedido se crea y queda pendiente de pago; la confirmación llega
	 * cuando la pasarela (Webpay/Khipu/Mercado Pago) esté conectada.
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wc_add_notice( __( 'No se pudo crear el pedido. Inténtalo de nuevo.', 'clinicalskin' ), 'error' );
			return array( 'result' => 'failure' );
		}

		$order->update_status( 'pending', sprintf( __( 'Esperando el pago con %s.', 'clinicalskin' ), $this->title ) );

		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}

/** Tarjeta de crédito o débito (Webpay): con los campos del diseño. */
class CS_Gateway_Webpay extends CS_Gateway_Pago {

	public function __construct() {
		parent::__construct(
			'webpay',
			__( 'Tarjeta de crédito o débito', 'clinicalskin' ),
			__( 'Visa, Mastercard y American Express vía Transbank', 'clinicalskin' ),
			true
		);
	}

	/**
	 * Bloque .card-fields del checkout.html. Los inputs no llevan name:
	 * son maquetación (el pago se completa en Webpay) y por eso mismo no
	 * se envían con el formulario ni se guardan en el pedido.
	 */
	public function payment_fields() {
		?>
		<div class="field-grid">
			<div class="field full">
				<label for="cs-tarjeta">Número de tarjeta</label>
				<input id="cs-tarjeta" type="text" placeholder="0000 0000 0000 0000" inputmode="numeric" autocomplete="cc-number">
			</div>
			<div class="field">
				<label for="cs-venc">Vencimiento MM/AA</label>
				<input id="cs-venc" type="text" placeholder="08/29" autocomplete="cc-exp">
			</div>
			<div class="field">
				<label for="cs-cvv">CVV</label>
				<input id="cs-cvv" type="text" placeholder="123" inputmode="numeric" autocomplete="cc-csc">
			</div>
			<div class="field full">
				<label for="cs-titular">Nombre en la tarjeta</label>
				<input id="cs-titular" type="text" placeholder="COMO APARECE EN LA TARJETA" autocomplete="cc-name">
			</div>
		</div>
		<p class="secure-note">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
			Serás redirigido a Webpay (Transbank) para completar el pago de forma segura.
		</p>
		<?php
	}
}

/** Khipu: transferencia bancaria. */
class CS_Gateway_Khipu extends CS_Gateway_Pago {

	public function __construct() {
		parent::__construct(
			'khipu',
			__( 'Transferencia bancaria', 'clinicalskin' ),
			__( 'Desde cualquier banco · se acredita al instante', 'clinicalskin' ),
			false
		);
	}
}

/** Mercado Pago. */
class CS_Gateway_Mercado extends CS_Gateway_Pago {

	public function __construct() {
		parent::__construct(
			'mercado',
			__( 'Mercado Pago', 'clinicalskin' ),
			__( 'Hasta 3 cuotas sin interés', 'clinicalskin' ),
			false
		);
	}
}

/* Los del diseño van primero: la tarjeta queda seleccionada por defecto. */
add_filter(
	'woocommerce_payment_gateways',
	function ( $gateways ) {
		return array_merge(
			array( 'CS_Gateway_Webpay', 'CS_Gateway_Khipu', 'CS_Gateway_Mercado' ),
			(array) $gateways
		);
	}
);

/* El gateway real de Transbank toma el aspecto de la fila del diseño: chip
   WEBPAY, título "Tarjeta de crédito o débito" y la nota de redirección. */
add_filter(
	'cs_pagos_chips',
	function ( $chips ) {
		$chips['transbank_webpay_plus_rest'] = 'WEBPAY';
		return $chips;
	}
);

add_filter(
	'cs_pagos_subtitulos',
	function ( $subs ) {
		$subs['transbank_webpay_plus_rest'] = __( 'Visa, Mastercard y American Express vía Transbank', 'clinicalskin' );
		return $subs;
	}
);

add_filter(
	'cs_pagos_nota',
	function ( $nota, $id ) {
		if ( 'transbank_webpay_plus_rest' === $id ) {
			return __( 'Serás redirigido a Webpay (Transbank) para completar el pago de forma segura.', 'clinicalskin' );
		}
		return $nota;
	},
	10,
	2
);

/* El plugin lo llama "Webpay Plus"; en el checkout va el título del diseño. */
add_filter(
	'woocommerce_gateway_title',
	function ( $titulo, $id ) {
		if ( 'transbank_webpay_plus_rest' === $id ) {
			return __( 'Tarjeta de crédito o débito', 'clinicalskin' );
		}
		return $titulo;
	},
	10,
	2
);
