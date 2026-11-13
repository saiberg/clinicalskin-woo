/* ============================================================
   Clinical Skin — Catálogo en tiempo real (Fase 2)
   ------------------------------------------------------------
   Consume el endpoint wc-ajax=cs_catalogo (functions.php) que
   devuelve los 42 productos reales de WooCommerce con sus
   atributos cosméticos (paso, tipos, preocupaciones, intensidad,
   frecuencia) y metadatos de texto (porque, ficha, modo de uso,
   ingredientes).

   Cachea la respuesta en localStorage con TTL corto para no
   golpear el servidor en cada carga. Expone window.CS_CATALOGO
   con una API basada en promesas:

     CS_CATALOGO.cargar()        → Promise<{productos, total, ...}>
     CS_CATALOGO.productos()     → Promise<Array>  (atajo)
     CS_CATALOGO.limpiarCache()  → borra la caché
     CS_CATALOGO.porId(id)       → Promise<producto|null>

   Si el endpoint falla, devuelve la caché vencida (si existe) o
   un array vacío, para que el test no se rompa.
   ============================================================ */
(() => {
  'use strict';

  const CLAVE = 'cs_catalogo_cache';
  const TTL_MS = 60 * 60 * 1000; // 1 hora
  const ENDPOINT = '/?wc-ajax=cs_catalogo';

  let enVuelo = null;

  const leerCache = () => {
    try {
      const raw = localStorage.getItem(CLAVE);
      if (!raw) return null;
      const data = JSON.parse(raw);
      if (!data || !Array.isArray(data.productos)) return null;
      return data;
    } catch (e) {
      return null;
    }
  };

  const escribirCache = (data) => {
    try {
      localStorage.setItem(CLAVE, JSON.stringify({ ...data, guardado: Date.now() }));
    } catch (e) {
      /* modo privado o cuota llena: se ignora */
    }
  };

  const vigente = (data) => data && typeof data.guardado === 'number' && Date.now() - data.guardado < TTL_MS;

  const pedir = async () => {
    const r = await fetch(ENDPOINT, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    const json = await r.json();
    if (!json || !json.ok || !Array.isArray(json.productos)) throw new Error('Respuesta inválida');
    return json;
  };

  const cargar = async (forzar = false) => {
    const cache = leerCache();
    if (!forzar && vigente(cache)) return cache;

    if (enVuelo) return enVuelo;

    enVuelo = (async () => {
      try {
        const data = await pedir();
        escribirCache(data);
        return data;
      } catch (e) {
        /* Sin red o error del servidor: mejor caché vencida que nada. */
        return cache || { ok: false, productos: [], total: 0, error: String(e) };
      } finally {
        enVuelo = null;
      }
    })();

    return enVuelo;
  };

  const productos = async (forzar = false) => {
    const data = await cargar(forzar);
    return data.productos || [];
  };

  const porId = async (id) => {
    const lista = await productos();
    return lista.find((p) => p.id === id || p.sku === id) || null;
  };

  const limpiarCache = () => {
    try {
      localStorage.removeItem(CLAVE);
    } catch (e) {
      /* sin acceso */
    }
  };

  window.CS_CATALOGO = { cargar, productos, porId, limpiarCache, CLAVE, TTL_MS };
})();
