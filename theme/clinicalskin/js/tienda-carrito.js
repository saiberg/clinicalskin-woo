/* ============================================================
   Botón "Añadir al carrito" en las tarjetas de la tienda
   ------------------------------------------------------------
   Añade por AJAX (wc-ajax=add_to_cart) sin recargar la página y
   muestra confirmación en el propio botón. Si falla, usa el enlace
   normal de WooCommerce.
   ============================================================ */
(() => {
  'use strict';

  document.addEventListener('click', async (ev) => {
    const btn = ev.target && ev.target.closest ? ev.target.closest('.cs-add-to-cart') : null;
    if (!btn) return;

    const id = btn.getAttribute('data-product-id');
    if (!id) return;

    ev.preventDefault();
    ev.stopPropagation();

    const textoOriginal = btn.textContent;
    btn.classList.add('is-loading');
    btn.setAttribute('aria-busy', 'true');
    btn.textContent = 'Añadiendo…';

    try {
      const body = new URLSearchParams({ product_id: id, quantity: '1' });
      const r = await fetch('/?wc-ajax=add_to_cart', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      });
      const j = await r.json();

      if (j && j.error) {
        btn.classList.remove('is-loading');
        btn.removeAttribute('aria-busy');
        btn.textContent = 'No disponible';
        setTimeout(() => { btn.textContent = textoOriginal; }, 1800);
        return;
      }

      /* Refresca el contador/fragmentos del carrito del header. */
      if (j && j.fragments) {
        Object.keys(j.fragments).forEach((sel) => {
          document.querySelectorAll(sel).forEach((el) => { el.outerHTML = j.fragments[sel]; });
        });
      }
      document.body.dispatchEvent(new CustomEvent('wc_fragments_refreshed'));

      btn.classList.remove('is-loading');
      btn.removeAttribute('aria-busy');
      btn.classList.add('is-added');
      btn.textContent = '✓ Añadido';
      setTimeout(() => {
        btn.classList.remove('is-added');
        btn.textContent = textoOriginal;
      }, 2000);
    } catch (e) {
      /* Sin red: seguir el enlace normal de WooCommerce. */
      window.location.href = btn.getAttribute('href');
    }
  }, true);
})();