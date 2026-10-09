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

  const esperar = (ms) => new Promise((ok) => setTimeout(ok, ms));

  const nombresDeLaRutina = () => {
    const nuevo = document.querySelectorAll('.rp-card:not(.off) h3');
    const fuente = nuevo.length ? nuevo : document.querySelectorAll('.routine-block .products-grid h3');
    return Array.from(fuente).map((h) => h.textContent.trim());
  };

  /* id → nombre del catálogo, para comparar con lo que devuelve la bolsa */
  const nombreDe = {};
  Object.keys(datos.productos).forEach((n) => {
    nombreDe[datos.productos[n]] = n;
  });

  const alta = async (id) => {
    try {
      await fetch('/?wc-ajax=add_to_cart', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: 'product_id=' + id + '&quantity=1'
      });
    } catch (e) {
      /* sin red: lo retoma la ronda de revisión */
    }
  };

  /* La sesión de WooCommerce se escribe al terminar cada petición: si la
     siguiente empieza antes, pisa el carrito y se pierde un producto
     (afloraba de forma aleatoria, uno distinto por intento). Por eso hay
     pausa entre altas y una revisión final que vuelve a pedir los que
     falten. */
  const nombresEnBolsa = async () => {
    try {
      const r = await fetch('/?wc-ajax=get_refreshed_fragments', { credentials: 'same-origin' });
      const j = await r.json();
      const html = j.fragments ? j.fragments['div.widget_shopping_cart_content'] : '';
      if (!html) return null;
      const doc = new DOMParser().parseFromString(html, 'text/html');
      return Array.from(doc.querySelectorAll('.mini_cart_item a'))
        .filter((a) => !a.classList.contains('remove'))
        .map((a) => a.textContent.trim());
    } catch (e) {
      return null;
    }
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

      (async () => {
        let pendientes = ids;
        for (let ronda = 0; ronda < 3 && pendientes.length; ronda++) {
          for (const id of pendientes) {
            await alta(id);
            await esperar(300);
          }
          const enBolsa = await nombresEnBolsa();
          if (!enBolsa) break;
          pendientes = pendientes.filter((id) => {
            const nombre = nombreDe[id];
            /* sin nombre comparable no se vuelve a añadir (evita duplicar) */
            return nombre ? enBolsa.indexOf(nombre) === -1 : false;
          });
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
