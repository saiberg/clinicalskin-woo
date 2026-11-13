# Test Digital de Piel — Documentación de Funcionamiento

> Guía técnica y funcional del Test Digital de Piel de Clinical Skin.
> Última actualización: 2026-11-13
> Estado: implementado (Fases 0-7 completadas).

---

## 1. Qué es

Un cuestionario inteligente de **6 preguntas** que identifica el tipo de piel y
las preocupaciones del cliente, calcula un perfil con **reglas deterministas** y
recomienda una **rutina de productos reales** de WooCommerce (básica o avanzada),
con motivo de recomendación y conexión directa al carrito.

**No es un diagnóstico médico.** Es orientación cosmética.

---

## 2. Flujo del usuario

```
1. Bienvenida (diagnostico.html)
   ↓
2. Q1 — Tipo de piel (1 opción): normal / mixta / seca / grasa / sensible
   ↓
3. Q2 — Preocupaciones (selección múltiple, 11 opciones)
   ↓
4. Q3 — Rutina actual (1 opción)
   ↓
5. Q4 — Sol y clima (1 opción)
   ↓
6. Q5 — Objetivo (1 opción)
   ↓
7. Q6 — Hábitos (protector solar, sin fragancia, rutina simple/completa)
   ↓
8. Análisis (animación) → guarda el perfil en localStorage
   ↓
9. Resultados (resultado.html)
   - Hero con perfil y tags
   - Indicadores (5 barras 0-100)
   - Rutina AM/PM con alternancia básica/avanzada
   - Calendario semanal + consejo
   - Captura de email (opcional)
   - WhatsApp con mensaje prefijado
   ↓
10. Añadir rutina al carrito → WooCommerce (/cart/) con -10% si 2+ productos
```

---

## 3. Arquitectura

### 3.1 Archivos

| Archivo | Rol |
|---|---|
| `static/diagnostico.html` | Pantalla del test |
| `static/resultado.html` | Pantalla de resultados |
| `static/js/logica.js` | **Motor de reglas** (`CS_DIAG`) |
| `static/js/diagnostico-pro.js` | Interfaz del test |
| `static/js/resultado-pro.js` | Render de resultados + compra |
| `static/css/diagnostico.css` | Estilos del test |
| `static/css/resultado.css` | Estilos de resultados |
| `theme/clinicalskin/functions.php` | Endpoint catálogo, redirecciones, cupón |
| `theme/clinicalskin/js/catalogo.js` | Cliente del catálogo (caché) |
| `theme/clinicalskin/js/puente-diagnostico.js` | Puente resultado → carrito |
| `theme/clinicalskin/footer.php` | Orden de carga de scripts |

### 3.2 Cómo se sirve

El sitio estático se monta en el contenedor de WordPress (`docker-compose.yml`).
`cs_piezas()` lee el HTML estático y lo inyecta en el tema. Los `.html` se
resuelven por rewrite de WordPress.

### 3.3 Orden de carga de scripts (crítico)

```
catalogo.js  →  logica.js  →  diagnostico-pro.js / resultado-pro.js  →  puente
```

`catalogo.js` debe cargarse **antes** que `logica.js` para que la sincronización
del catálogo real esté lista al calcular la rutina. Se imprime desde `footer.php`.

---

## 4. Modelo de datos

### 4.1 Respuestas (localStorage `cs_diagnostico`)

```json
{
  "q1": "mixta",
  "q2": ["imperfecciones", "poros"],
  "q3": "basica",
  "q4": "moderado",
  "q5": "brillo_poros",
  "q6": { "protector": true, "sin_fragancia": false, "preferencia": "simple" }
}
```

Caduca a los **30 días**.

### 4.2 Las 11 preocupaciones

`hidratacion` · `luminosidad` · `textura` · `poros` · `sebo` · `imperfecciones` ·
`manchas` · `lineas` · `firmeza` · `sensibilidad` · `contorno`

Alias de compatibilidad: `acne → imperfecciones`, `deshidratacion → hidratacion`,
`edad → lineas`.

### 4.3 Perfil derivado

`tipo`, `preoc1`, `preoc2`, `preocupaciones[]`, `rutina`, `sol`, `sensibilidad`
(alta/media/baja), `tolerancia` (alta/media/baja), `pasos`, `hidratacion`,
`spfPrioridad`, `sinFragancia`, `rutinaCompleta`, `habitos`.

---

## 5. Motor de reglas

### 5.1 Indicadores (0-100 o null)

| Indicador | Base por tipo | Ajustes |
|---|---|---|
| hidratación | normal 70 · seca 30 · grasa 55 · mixta 55 · sensible 45 | −15 si preoc `hidratacion` |
| sebo | normal 50 · seca 25 · grasa 85 · mixta 65 · sensible 40 | +10 si `sebo`/`imperfecciones`/`poros` |
| sensibilidad | normal 20 · seca 45 · grasa 25 · mixta 35 · sensible 80 | +10 si `sensibilidad`; +10 si sol `reactivo_clima` |
| exposición solar | bajo 25 · moderado 60 · alto 90 · reactivo 55 | — |
| tolerancia | alta 85 · media 60 · baja 35 | — |

Si no hay información → `null` (no se inventa).

### 5.2 Rutina

**Orden de pasos esenciales**: limpieza → tratamiento → hidratación → protección
solar → contorno (si se pidió) → extras.

- **Básica**: presupuesto 4 pasos.
- **Avanzada**: presupuesto según Q3 (4/5/6) + extras (exfoliante BHA, refuerzo,
  reparación).

### 5.3 Stock y agotados

- El catálogo se sincroniza desde el endpoint `cs_catalogo`.
- `ponerPaso` usa el primer candidato **disponible y facial**.
- Filtro facial: excluye `Champú`, `Cuidado corporal`, `Labios`.
- Si un paso esencial no tiene stock, se incluye el preferido **marcado agotado**
  (sin compra).
- Los agotados nunca entran en el total ni en el carrito.

### 5.4 Match %

`85 base + 8 tipo + 10 preocupación` → tope 99.

---

## 6. Integración con WooCommerce

### 6.1 Endpoint del catálogo

`GET /?wc-ajax=cs_catalogo` → JSON con los 42 productos:

```json
{
  "ok": true, "total": 42, "productos": [{
    "id": "sku", "product_id": 190, "nombre": "...", "precio": 32990,
    "img": "...", "url": "...", "stock": { "cantidad": 1, "en_stock": true },
    "categorias": ["Crema", "EUCERIN"], "paso": "hidratacion",
    "tipos": ["seca"], "preocupaciones": ["hidratacion"], "intensidad": "baja",
    "frecuencia": "manana-y-noche", "desc": "...", "porque": "...",
    "ficha": "...", "modo_uso": "...", "ingredientes": "..."
  }]
}
```

Caché en `localStorage` (`cs_catalogo_cache`) con TTL de **1 hora**.

### 6.2 Atributos cosméticos en WooCommerce

Atributos globales: `pa_paso`, `pa_tipo-de-piel`, `pa_preocupacion`,
`pa_intensidad`, `pa_frecuencia`. Metadatos: `cs_desc`, `cs_porque`, `cs_ficha`,
`cs_modo_uso`, `cs_ingredientes`.

### 6.3 Carrito y descuento

- El puente añade los productos por `data-product-id` → `data-sku` → nombre.
- Excluye agotados y desmarcados.
- Marca los ids con `wc-ajax=cs_rutina`.
- El cupón **`rutina` (-10%)** se aplica automáticamente con 2+ productos de la
  rutina del diagnóstico. **No** aplica a productos sueltos de la tienda.

---

## 7. Extensión futura (IA fotográfica)

`CS_DIAG.analisisVisual` (deshabilitado por defecto):

```js
CS_DIAG.analisisVisual.registrar(async (file, ctx) => ({
  ok: true, fuente: 'proveedor', indicadores: { hidratacion: 42, ... },
  preocupaciones: ['sebo'], confianza: 0.7
}));
```

La foto es **siempre opcional**. Ver `docs/logica-diagnostico.md` §12 y
`docs/ejemplo-ia-visual.js`.

---

## 8. Mantenimiento

| Tarea | Dónde |
|---|---|
| Cambiar reglas/umbrales | `CONFIG` en `js/logica.js` |
| Cambiar copys | `CONFIG.resumen`, `CONFIG.tips` en `js/logica.js` |
| Añadir preocupación | `CONFIG.preocupaciones` + `VALORES[2]` + UI en `diagnostico-pro.js` |
| Cambiar productos de rutina | `SKU` en `js/logica.js` |
| Repoblar atributos | `scripts/cs-poblar-atributos.php` + `cs-fix-frecuencia.php` |
| Verificar poblado | `scripts/cs-verificar-poblado.php` |

---

## 9. Verificación rápida

```powershell
# Endpoint del catálogo
Invoke-WebRequest "http://localhost:8080/?wc-ajax=cs_catalogo" -UseBasicParsing

# Páginas
Invoke-WebRequest "http://localhost:8080/diagnostico.html" -UseBasicParsing
Invoke-WebRequest "http://localhost:8080/resultado.html" -UseBasicParsing

# Sintaxis JS
node --check "C:\xampp\htdocs\clinicalskin\js\logica.js"

# Sintaxis PHP
docker compose run --rm cli php -l /var/www/html/wp-content/themes/clinicalskin/functions.php
```

---

## 10. Respaldos

Cada fase tiene respaldo en `parches/backup-<fase>-<timestamp>/`:
- `backup-20261113-115654` (Fase 0, incluye dump SQL)
- `backup-fase3-*`, `backup-fase4-*`, `backup-fase5-*`, `backup-fase7-*`
