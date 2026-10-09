/* ============================================================
   Clinical Skin — Puente: diagnóstico → carrito de WooCommerce
   ------------------------------------------------------------
   El resultado (resultado.html) es del sitio estático: el botón de
   compra guarda en localStorage.cs_cart y va a carrito.html, rutas
   que aquí ya se redirigen a WooCommerce. Este archivo reutiliza ese
   mismo botón para meter los productos en la bolsa real y llevar a
   /cart/, que es donde se paga.

   Dos marcas de botón, porque el resultado cambió de diseño:
   - [data-rp-add]      → "Añadir rutina al carrito" (resultado-pro.js)
   - [data-add-routine] → "Añadir rutina completa"  (compra.js, versión anterior)

   Los nombres salen del listado activo: .rp-card (las que el usuario
   desmarcó quedan en .off y no se compran) o, si no existe, del
   listado viejo .routine-block .products-grid.

   Se engancha en la fase de captura sobre document, así que se
   registra antes que js/compra.js / js/resultado-pro.js y sus
   manejores de localStorage no llegan a ejecutarse.
   ============================================================ */
(() => {
  'use strict';

  const datos = window.__csDiagnosticoWoo;
  if (!datos || !datos.productos || !datos.carrito) return;

  const nombresDeLaRutina = () => {
    const nuevo = document.querySelectorAll('.rp-card:not(.off) h3');
    const fuente = nuevo.length ? nuevo : document.querySelectorAll('.routine-block .products-grid h3');
    return Array.from(fuente).map((h) => h.textContent.trim());
  };

  document.addEventListener(
    'click',
    (ev) => {
      const target = ev.target && ev.target.closest ? ev.target : null;
      const btn = target && target.closest('[data-rp-add], [data-add-routine]');
      if (!btn) return;

      ev.preventDefault();
      ev.stopPropagation();

      /* Mismos productos que calcula el sitio estático para ese botón */
      const ids = [...new Set(nombresDeLaRutina().map((n) => datos.productos[n] || 0).filter(Boolean))];

      if (!ids.length) {
        location.href = datos.carrito;
        return;
      }

      btn.textContent = 'Añadiendo a tu bolsa…';

      /* wc-ajax=add_to_cart solo responde con POST (en GET devuelve vacío):
         los productos se añaden uno a uno; la sesión viaja en la cookie, así
         que basta con esperar cada respuesta antes de navegar. */
      (async () => {
        for (const id of ids) {
          try {
            await fetch('/?wc-ajax=add_to_cart', {
              method: 'POST',
              credentials: 'same-origin',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
              body: 'product_id=' + id + '&quantity=1'
            });
          } catch (e) {
            /* sin red: se sigue con el resto; el carrito muestra lo que haya */
          }
        }
        try {
          localStorage.removeItem('cs_cart');
        } catch (e) {
          /* modo privado */
        }
        location.href = datos.carrito;
      })();
    },
    true
  );
})();
