/**
 * Carrito: botones +/- de la cantidad y refresco automático del total.
 *
 * El formulario y sus botones los controla el JS de WooCommerce (wc-cart-js);
 * aquí solo hacemos que al cambiar la cantidad se pulse el botón
 * "update_cart", que es lo que dispara su refresco por AJAX.
 */
(function () {
	'use strict';

	var temporizador = null;
	var ESPERAS      = 15;
	var INTERVALO    = 450;

	/** Formulario del carrito (se sustituye completo tras cada refresco). */
	function formulario() {
		return document.querySelector('.woocommerce-cart-form');
	}

	function refrescar(intento) {
		var form = formulario();
		if (!form) {
			return;
		}
		// WooCommerce marca el formulario mientras hay una petición en vuelo.
		if (form.closest('.processing')) {
			if (intento < ESPERAS) {
				temporizador = setTimeout(function () {
					refrescar(intento + 1);
				}, 400);
			}
			return;
		}
		var boton = form.querySelector('button[name="update_cart"], input[name="update_cart"]');
		if (!boton) {
			return;
		}
		boton.disabled = false;
		boton.click();
	}

	function programar() {
		clearTimeout(temporizador);
		temporizador = setTimeout(function () {
			refrescar(0);
		}, INTERVALO);
	}

	/** Ajusta la cantidad del input vecino y avisa con un "change" nativo. */
	document.addEventListener(
		'click',
		function (evento) {
			var paso = evento.target && evento.target.closest ? evento.target.closest('.qty-step') : null;
			if (!paso) {
				return;
			}
			evento.preventDefault();

			var contenedor = paso.closest('.qty-selector');
			var input      = contenedor ? contenedor.querySelector('input.qty') : null;
			if (!input) {
				return;
			}

			var min    = input.min !== '' ? parseFloat(input.min) : 1;
			var max    = input.max !== '' ? parseFloat(input.max) : Infinity;
			var actual = parseFloat(input.value);
			var delta  = parseInt(paso.getAttribute('data-qty'), 10) || 0;

			if (isNaN(min)) {
				min = 1;
			}
			if (isNaN(actual)) {
				actual = min;
			}

			var nuevo = actual + delta;
			if (nuevo < min) {
				nuevo = min;
			}
			if (nuevo > max) {
				nuevo = max;
			}
			if (nuevo === actual) {
				return;
			}

			input.value = String(nuevo);
			input.dispatchEvent(new Event('change', { bubbles: true }));
		},
		false
	);

	/** Cantidad escrita a mano: mismo refresco, con una pausa para teclear. */
	document.addEventListener(
		'change',
		function (evento) {
			var input = evento.target;
			if (!input || !input.classList || !input.classList.contains('qty')) {
				return;
			}
			if (!input.closest('.woocommerce-cart-form')) {
				return;
			}
			programar();
		},
		false
	);

	/** Cabecera "Tu carrito · N productos": el HTML no se recarga en AJAX. */
	function actualizarContador() {
		var total = 0;
		var inputs = document.querySelectorAll('.woocommerce-cart-form input.qty');
		for (var i = 0; i < inputs.length; i++) {
			var valor = parseInt(inputs[i].value, 10);
			if (!isNaN(valor)) {
				total += valor;
			}
		}
		var eyebrow = document.querySelector('[data-cart-eyebrow]');
		if (eyebrow) {
			var texto = 'Tu carrito · ' + total + ' ' + (1 === total ? 'producto' : 'productos');
			if (eyebrow.textContent !== texto) {
				eyebrow.textContent = texto;
			}
		}
		if (typeof window.__csBolsa === 'function') {
			window.__csBolsa(total);
		}
	}

	actualizarContador();

	/* WooCommerce sustituye el carrito por AJAX: se recalcula al cambiar el DOM. */
	if (window.MutationObserver && document.body) {
		new MutationObserver(actualizarContador).observe(document.body, {
			childList: true,
			subtree: true
		});
	}
})();
