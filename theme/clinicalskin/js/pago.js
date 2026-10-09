/**
 * Checkout con el formato de checkout.html:
 *  - el botón de pago muestra el total ("Pagar $78.330")
 *  - el bloque de botón + condiciones se muda a la columna derecha, bajo el
 *    resumen del pedido (como en el diseño).
 *
 * WooCommerce sustituye .woocommerce-checkout-payment completo cada vez que se
 * actualizan los gastos de envío: el botón nuevo vuelve a la columna izquierda
 * y aquí se vuelve a mover. El nodo se mueve entero, así que conserva sus
 * manejadores y el nonce del formulario.
 */
(function ($) {
	'use strict';

	function total() {
		var celda = document.querySelector(
			'.woocommerce-checkout-review-order-table .order-total .woocommerce-Price-amount, .woocommerce-checkout-review-order-table .order-total .amount'
		);
		return celda ? celda.textContent.replace(/\s+/g, ' ').trim() : '';
	}

	function pintar() {
		var boton = document.getElementById('place_order');
		if (!boton) {
			return;
		}
		var monto = total();
		var texto = monto ? 'Pagar ' + monto : 'Pagar';
		if (boton.textContent.replace(/\s+/g, ' ').trim() !== texto) {
			boton.textContent = texto;
		}
	}

	function actualizar() {
		var origen = document.querySelector('.woocommerce-checkout-payment > .place-order');
		var destino = document.querySelector('[data-cs-pagar]');

		if (origen && destino && origen.parentNode !== destino) {
			destino.innerHTML = '';
			destino.appendChild(origen);
		}
		pintar();
	}

	/* La tarjeta elegida se resalta (clase .active, como .radio-card.selected
	   del diseño): WooCommerce solo alterna la caja, no la tarjeta. */
	function marcar() {
		var items = document.querySelectorAll('#payment ul.payment_methods > li');
		for (var i = 0; i < items.length; i++) {
			var radio = items[i].querySelector('input[name="payment_method"]');
			items[i].classList.toggle('active', !!(radio && radio.checked));
		}
	}

	$(function () {
		actualizar();
		marcar();
	});
	$(document.body).on('updated_checkout checkout_error wc_fragments_refreshed', actualizar);
	$(document.body).on('change', 'input[name="payment_method"]', marcar);
})(jQuery);
