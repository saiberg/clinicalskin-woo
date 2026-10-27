/* Respuesta visual inmediata al añadir un producto desde su ficha. */
(() => {
  'use strict';

  const guardarBoton = (boton) => {
    if (!boton || boton.dataset.csLoading === '1') return;
    boton.dataset.csLoading = '1';
    boton.dataset.csLabel = boton.textContent.trim();
    boton.setAttribute('aria-busy', 'true');
    boton.classList.add('cs-add-to-cart-loading');
    boton.innerHTML = '<span class="cs-cart-spinner" aria-hidden="true"></span><span>Añadiendo a tu bolsa…</span>';

    /* Si el navegador no inicia la navegación, recupera el botón para reintentar. */
    window.setTimeout(() => {
      if (!boton.isConnected || document.visibilityState === 'hidden') return;
      restaurarBoton(boton);
    }, 15000);
  };

  const restaurarBoton = (boton) => {
    if (!boton || boton.dataset.csLoading !== '1') return;
    boton.classList.remove('cs-add-to-cart-loading');
    boton.removeAttribute('aria-busy');
    boton.textContent = boton.dataset.csLabel || 'Añadir al carrito';
    delete boton.dataset.csLoading;
    delete boton.dataset.csLabel;
  };

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('form.cart')) return;
    if (typeof form.checkValidity === 'function' && !form.checkValidity()) return;

    const boton = event.submitter || form.querySelector('.single_add_to_cart_button');
    guardarBoton(boton);
  }, true);

  window.addEventListener('pageshow', () => {
    document.querySelectorAll('.single_add_to_cart_button.cs-add-to-cart-loading').forEach(restaurarBoton);
  });
})();
