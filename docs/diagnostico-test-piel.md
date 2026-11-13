# Test Digital de Piel — Diagnóstico y Plan de Mejora

> Documento de diagnóstico y propuesta de arquitectura para la mejora sustancial
> del Test Digital de Piel de Clinical Skin.
> Estado: **diagnóstico + plan** (aún no se modifica código).
> Fecha: 2026-11-13

---

## 1. Resumen ejecutivo

El Test Digital de Piel **ya existe y funciona** en producción. Es un cuestionario
de 5 preguntas que deriva un perfil de piel y una rutina de productos, conectada al
carrito real de WooCommerce. La mejora solicitada consiste en **ampliar y
profundizar** el test (más tipos de piel, más preocupaciones, rutina básica +
avanzada, indicadores documentados, integración con catálogo real en tiempo real y
preparación para IA fotográfica futura), **sin romper lo que ya funciona** y
**gastando lo mínimo**.

Punto de partida claro: el motor de reglas ya es determinista, explicable y
documentado. La mejora es **evolutiva**, no reconstructiva.

---

## 2. Arquitectura actual (verificada)

### 2.1 Dónde vive el código

El test **se sirve a través de WordPress**, pero su código fuente está en la carpeta
del sitio estático. Mecanismo de "puente" por montaje de volúmenes Docker
(`docker-compose.yml`):

| Ruta host (Windows) | Ruta contenedor | Rol |
|---|---|---|
| `C:\xampp\htdocs\clinicalskin` | `/var/www/html/static` | HTML estático leído por el tema |
| `C:\xampp\htdocs\clinicalskin\img` | `/var/www/html/img` | Imágenes |
| `C:\xampp\htdocs\clinicalskin\css` | `/var/www/html/css` | CSS |
| `C:\xampp\htdocs\clinicalskin\js` | `/var/www/html/js` | JavaScript |
| `C:\docker\clinicalskin-woo\theme\clinicalskin` | `/var/www/html/wp-content/themes/clinicalskin` | Tema puente |

- `cs_piezas()` ([functions.php](/C:/docker/clinicalskin-woo/theme/clinicalskin/functions.php:101))
  lee el `<head>`, `<main>`, `<footer>` y los `<script>` del HTML estático y los
  inyecta dentro de WordPress.
- Una regla de rewrite (`^([^/]+)\.html$`) convierte `diagnostico.html`,
  `resultado.html`, `producto.html`, etc. en páginas de WordPress.
- **Consecuencia:** editar `C:\xampp\htdocs\clinicalskin\js\logica.js` se refleja
  en WordPress **al instante** (volumen montado). No hay que "publicar" nada para
  ver cambios en desarrollo.

### 2.2 Archivos del test

| Archivo | Rol |
|---|---|
| `diagnostico.html` | Pantalla del test: bienvenida + 5 preguntas + análisis + resumen |
| `resultado.html` | Resultados: rutina AM/PM, calendario, captura de email, barra de compra |
| `js/logica.js` | **Motor de reglas** (`CS_DIAG`): `CONFIG`, `CATALOGO`, `calcularPerfil`, `armarRutina`, `matchProducto`, persistencia en `localStorage` |
| `js/diagnostico-pro.js` | Interfaz del test (preguntas, métricas en vivo, animaciones) |
| `js/resultado-pro.js` | Render de resultados + compra + captura de email |
| `css/diagnostico.css` | Estilos del escáner y resultado |
| `css/resultado.css` | Estilos de la página de resultados |
| `theme/clinicalskin/js/puente-diagnostico.js` | Puente: resultado → carrito WooCommerce real (agrega SKUs y aplica cupón "rutina" -10%) |
| `theme/clinicalskin/functions.php` | Redirecciones (`producto.html?id=<sku>` → ficha), descuento de rutina, enqueue de scripts |
| `docs/logica-diagnostico.md` (estático) | Documentación de reglas actual |

### 2.3 Modelo de datos actual

- **5 preguntas** (`PREGUNTAS` en `diagnostico-pro.js`, valores en `VALORES` de `logica.js`):

  | Q | Dimensión | Valores |
  |---|---|---|
  | Q1 | `tipo` | `mixta` · `seca` · `grasa` · `sensible` |
  | Q2 | `preoc1` | `acne` · `deshidratacion` · `manchas` · `edad` |
  | Q3 | `rutina` | `basica` · `multi` · `cambiante` · `ninguna` |
  | Q4 | `sol` | `bajo` · `moderado` · `alto` · `reactivo_clima` |
  | Q5 | `preoc2` (objetivo) | reutiliza enum de Q2 vía `mapObjetivo` |

- **Perfil derivado** (`calcularPerfil`): `tipo`, `preoc1`, `preoc2`,
  `preocupaciones`, `rutina`, `sol`, `sensibilidad` (alta/media/baja),
  `tolerancia` (alta/media/baja), `pasos` (4/5/6 según rutina), `hidratacion`,
  `spfPrioridad`.

- **Catálogo** (`CATALOGO`): ~11 productos dermocosméticos **hardcodeados** con
  `id` (= SKU), `nombre`, `paso` (limpieza/tratamiento/exfoliacion/hidratacion/proteccion),
  `tipos`, `preocupaciones`, `agresividad`, `frecuencia`, `precio`, `img`, `desc`,
  `porque`, `ficha`, `modoUso`, `ingredientes`.

- **Pipeline de rutina** (`armarRutina`): core fijo (limpieza → tratamiento →
  hidratación → SPF) + extras condicionales (exfoliante BHA, refuerzo, reparación)
  hasta alcanzar el presupuesto de pasos.

- **Match %** (`matchProducto`): `85 base + 8 tipo + 10 preocupación`, tope 99.

- **Persistencia**: `localStorage.cs_diagnostico`, caduca a 30 días.

### 2.4 Catálogo real de WooCommerce (verificado en vivo)

- **42 productos publicados** (coincide con README).
- Categorías por **tipo**: Protector solar (13), Crema (9), Cuidado corporal (5),
  Otros (5), Sérum (4), Contorno de ojos (2), Champú (2), Limpiador (2).
- Marcas: AVENE, EUCERIN, ISDIN, LRP, URIAGE, VICHY.
- Cada producto tiene `SKU`, `precio`, `stock` (inicial 1), `nombre`, `imagen`,
  `descripción corta` (primera frase), categorías (marca + tipo).
- El SKU del catálogo real **coincide** con el `id` del `CATALOGO` estático
  (ej. `eucerin-gel-limpiador-dermopure-ultra-150ml`), lo que ya permite el
  mapeo nombre→id que hace `puente-diagnostico.js`.

---

## 3. Brecha frente al prompt maestro

| Requisito del prompt | Estado actual | Brecha |
|---|---|---|
| 5 tipos de piel (normal, seca, grasa, mixta, sensible) | 4 tipos (falta **normal**) | Añadir tipo `normal` |
| Etapa 3: 11 preocupaciones (hidratación, luminosidad, textura, poros, sebo, imperfecciones, manchas, arrugas, firmeza, sensibilidad, contorno) | 4 preocupaciones | Ampliar a 11, con **selección múltiple** |
| Etapa 4: hábitos (protector solar, sin fragancia, rutina simple/completa) | Parcial (rutina + sol) | Añadir preferencia sin fragancia y simple/completa |
| Etapa 5: indicadores individuales con lógica documentada | Métricas visuales heurísticas | Documentar y formalizar puntuaciones |
| Etapa 6: rutina **básica** Y **avanzada** (dos alternativas) | Una sola rutina AM/PM | Añadir alternancia básica/avanzada |
| Etapa 7: precio/foto/nombre/stock **reales de WooCommerce** | Catálogo estático duplicado | Conectar en tiempo real |
| Etapa 8: WhatsApp, guardar resultados, cambiar rutina | Email + carrito (sin WhatsApp, sin alternar) | Añadir WhatsApp y alternancia |
| Preparación IA fotográfica futura | No hay estructura | Definir contrato de extensión |

---

## 4. Propuesta de arquitectura económica

### 4.1 Principios (del prompt)

- Herramientas gratuitas / ya disponibles.
- Aprovechar WordPress + WooCommerce existentes.
- Sin suscripciones, sin IA de pago, sin servicios externos con costo.
- No romper lo que funciona.

### 4.2 Decisiones de arquitectura

**D1. El motor de reglas sigue en JS puro (cliente).**
El cálculo de perfil y rutina es determinista y barato. No requiere servidor ni
base de datos. Se mantiene en `js/logica.js`, ampliado. Esto es **costo cero** y
escalable.

**D2. Catálogo real en tiempo real vía endpoint AJAX/WP REST propio.**
El `CATALOGO` estático se reemplaza por una fuente de datos viva. Opciones:

- **Opción A (recomendada): endpoint AJAX del tema.** Un `wc_ajax_cs_catalogo`
  (o `wp_ajax_nopriv`) en `functions.php` devuelve JSON con `id`, `sku`, `nombre`,
  `precio`, `img`, `stock`, `categorías` de los productos. El frontend lo consume
  con `fetch` y lo cachea en `localStorage` (TTL corto, p. ej. 1 h) para no
  golpear el servidor en cada visita.
- **Opción B: WP REST API nativa** (`/wp-json/wc/store/v1/products`). Ya existe,
  pero requiere manejar autenticación/permisos y no expone los atributos
  personalizados del diagnóstico (paso, tipos, preocupaciones) sin trabajo extra.

**Se elige Opción A**: un endpoint propio mínimo, sin dependencias nuevas, que
además puede inyectar los **atributos cosméticos** del diagnóstico (paso, tipos,
preocupaciones, agresividad) que hoy viven hardcodeados. Estos atributos se
almacenarán como **metadatos de producto** o **taxonomías** en WooCommerce.

**D3. Atributos cosméticos del diagnóstico viven en WooCommerce.**
Los campos `paso`, `tipos`, `preocupaciones`, `agresividad`, `porque`, `ficha`,
`modoUso`, `ingredientes` pasan a ser **metadatos de producto** (o taxonomías
personalizadas) en WooCommerce. Así hay **una sola fuente de verdad** y el
`CATALOGO` estático desaparece. El endpoint AJAX los expone junto con los datos
nativos.

**D4. La lógica de evaluación (reglas) se separa de los datos.**
`CONFIG` (reglas, umbrales, etiquetas, copys) sigue en JS y es **documentada**.
Los datos de productos vienen del endpoint. Esto cumple "documentar las reglas
para modificarlas en el futuro".

**D5. Preparación para IA fotográfica futura.**
Se define un **contrato de extensión** (interfaz) sin implementarlo: un hook
`CS_DIAG.analisisVisual` opcional que, si existe, recibe la imagen (futura) y
devuelve indicadores adicionales que se fusionan al perfil. La carga de foto será
opcional y no bloqueante. No se contrata ni construye IA ahora.

### 4.3 Costos

| Concepto | Costo |
|---|---|
| Motor de reglas ampliado (JS) | $0 |
| Endpoint AJAX propio (tema) | $0 |
| Metadatos/taxonomías en WooCommerce | $0 |
| WhatsApp (enlace `wa.me` con mensaje prefijado) | $0 |
| Captura de email (ya existe, `LEADS_ENDPOINT` vacío) | $0 (requiere SMTP futuro) |
| IA fotográfica | **No en esta etapa** |

**Total: $0 de inversión nueva.** No se requieren plugins ni suscripciones.

---

## 5. Lista de cambios necesarios (plan de implementación)

### Fase 0 — Respaldos (obligatorio antes de tocar)

1. Copia de seguridad de los archivos del test (`diagnostico.html`,
   `resultado.html`, `js/*.js`, `css/*.css`).
2. Copia de seguridad de `theme/clinicalskin/functions.php` y
   `theme/clinicalskin/js/puente-diagnostico.js`.
3. (Opcional) `docker compose exec db mysqldump` de la base de datos.

### Fase 1 — Modelo de datos cosmético en WooCommerce

> **Estado: ✅ completada.** Atributos ampliados (tipo-de-piel +normal,
> preocupación +7, nuevo atributo frecuencia) y 42/42 productos poblados con
> atributos cosméticos + metadatos `cs_*`. Stock/precios/categorías intactos.
> Scripts: `scripts/cs-poblar-atributos.php`, `scripts/cs-fix-frecuencia.php`,
> `scripts/cs-verificar-poblado.php`.

1. Definir taxonomías personalizadas (o metadatos) para:
   - `paso` (limpieza, tratamiento, exfoliacion, hidratacion, proteccion).
   - `tipos` (normal, seca, grasa, mixta, sensible, todo).
   - `preocupaciones` (11 valores).
   - `agresividad` (baja, media, alta).
   - `porque`, `ficha`, `modoUso`, `ingredientes`, `frecuencia`.
2. Script de migración: poblar estos atributos en los 42 productos (partiendo de
   los 11 ya mapeados en `CATALOGO` y extendiendo al resto según corresponda).
3. Verificar que no se altera stock ni catálogo (regla del README).

### Fase 2 — Endpoint de catálogo en tiempo real

> **Estado: ✅ completada.** Endpoint `wc_ajax_cs_catalogo` (también
> `wp_ajax_cs_catalogo` y `wp_ajax_nopriv_cs_catalogo`) en `functions.php`
> devuelve los 42 productos con atributos cosméticos y metadatos. Módulo cliente
> `theme/clinicalskin/js/catalogo.js` (`window.CS_CATALOGO`) con caché en
> `localStorage` (TTL 1 h). Verificado: 42 productos, 0 campos vacíos, 9 agotados
> reportados, 0 fetches en segunda carga.

1. En `functions.php`: registrar `wc_ajax_cs_catalogo` (y `wp_ajax_nopriv_...`)
   que devuelva JSON con los 42 productos + atributos cosméticos.
2. Exponer solo datos públicos (nombre, precio, imagen, stock, categorías,
   atributos cosméticos). Sin datos personales.
3. Cachear en `localStorage` con TTL (p. ej. 1 h) para no saturar.

### Fase 3 — Ampliar el modelo del test (logica.js)

> **Estado: ✅ completada.** Tipo `normal` añadido; 11 preocupaciones con
> selección múltiple; hábitos (Q6: protector, sin fragancia, simple/completa);
> `calcularIndicadores` documentado; `armarRutinaBasica` + `armarRutinaAvanzada`;
> exclusión por stock con fallback y marcado de agotados; sincronización con el
> endpoint. UI del test (`diagnostico-pro.js`) y resultados (`resultado-pro.js`)
> actualizadas. Probado en navegador con perfiles normal/grasa/sensible/seca/mixta.

1. Añadir tipo de piel **`normal`** (Q1 pasa de 4 a 5 opciones).
2. Ampliar preocupaciones de 4 a **11** con **selección múltiple** (Etapa 3):
   hidratación, luminosidad, textura irregular, poros visibles, exceso de sebo,
   imperfecciones, manchas/tono desigual, líneas de expresión, firmeza,
   sensibilidad/rojeces, contorno de ojos.
3. Añadir hábitos/preferencias (Etapa 4): uso de protector solar, preferencia
   sin fragancia, rutina simple vs. completa.
4. Formalizar y **documentar las puntuaciones** de indicadores (Etapa 5): definir
   reglas de cálculo explícitas y coherentes; no asignar puntuación si no hay
   información suficiente.
5. Ampliar el **pipeline de rutina** para generar **dos alternativas**:
   - **Rutina básica**: limpieza + hidratación + SPF (+ tratamiento esencial).
   - **Rutina avanzada**: limpieza + sérum/tratamiento + hidratación +
     complementarios + SPF.
6. Añadir regla de **exclusión por stock agotado** (no recomendar agotados).

### Fase 4 — Interfaz (diagnostico.html + diagnostico-pro.js)

> **Estado: ✅ completada.** Flujo por etapas (6 preguntas), selección múltiple
> de 11 preocupaciones, hábitos (Q6), indicadores documentados en resultados,
> alternancia **rutina básica/avanzada**, botón de WhatsApp con mensaje prefijado
> y badge de producto agotado. Identidad visual mantenida (marfil/dorado/cobre).
> Responsive verificado.

1. Rediseñar el flujo por etapas (Bienvenida → Tipo → Preocupaciones → Hábitos →
   Análisis → Resultado → Rutinas → Productos).
2. Selección múltiple de preocupaciones (chips/tarjetas).
3. Mostrar indicadores con lógica documentada.
4. Alternancia básica/avanzada en resultados.
5. Botón de WhatsApp con mensaje prefijado.
6. Mantener identidad visual (marfil, beige champagne, dorado, cobre, mármol).

### Fase 5 — Resultados (resultado.html + resultado-pro.js)

> **Estado: ✅ completada.** Cada producto muestra foto real, nombre, precio,
> descripción breve, ficha, motivo de recomendación, activo clave, modo de uso,
> "Ver ficha" y "Comprar" (no aparece en agotados). Enlaces a la ficha real de
> WooCommerce. Textos escapados (XSS-safe). Barra de compra con total y -10%.

1. Render de rutina básica y avanzada con toggle.
2. Productos con **datos reales** (foto, nombre, precio, stock, descripción,
   beneficio, motivo, modo de uso).
3. Botón "consultar/comprar" por producto y "añadir rutina al carrito".
4. Conservar captura de email y barra de compra.
5. WhatsApp para orientación.

### Fase 6 — Puente a WooCommerce (puente-diagnostico.js + functions.php)

> **Estado: ✅ completada.** El puente usa `data-sku`/`data-product-id` (con
> fallback a nombre), excluye agotados (`.rp-card.agotado`), deduplica y mantiene
> el cupón "rutina" -10% solo para la rutina del diagnóstico. `functions.php`
> inyecta `porSku` (SKU → product_id). Verificado: 4 productos añadidos, agotado
> excluido, -10% aplicado solo a la rutina (no a productos sueltos).

1. Adaptar el puente para que use los SKU reales devueltos por el endpoint
   (hoy ya mapea nombre→id; se simplifica al tener ids directos).
2. Mantener el cupón "rutina" (-10%) para 2+ productos.
3. Verificar que el descuento sigue aplicando solo a la rutina del diagnóstico.

### Fase 7 — Preparación IA fotográfica (contrato, sin implementar)

> **Estado: ✅ completada.** `CONFIG.analisisVisual` (deshabilitado por defecto) y
> `CS_DIAG.analisisVisual` con `registrar/analizar/fusionarIndicadores/
> fusionarPreocupaciones/analizarYFusionar`. Contrato documentado en
> `docs/logica-diagnostico.md` §12 y ejemplo en `docs/ejemplo-ia-visual.js`.
> Probado: sin analizador no rompe; fusión con peso 0.5; captura de errores.

1. Definir interfaz `CS_DIAG.analisisVisual` (opcional) en la documentación.
2. Dejar el flujo preparado para añadir carga de foto opcional sin reconstruir.

### Fase 8 — Documentación

> **Estado: ✅ completada.** `docs/test-piel-funcionamiento.md` (guía técnica y
> funcional), `docs/plugins-servicios-costos.md` (inventario real y costos),
> `docs/logica-diagnostico.md` §11-12 (reglas y contrato IA).

1. Actualizar `docs/logica-diagnostico.md` con las nuevas reglas y puntuaciones.
2. Documentar el endpoint AJAX y el contrato de IA.
3. Listado de plugins/servicios/costos (ver §4.3).

---

## 6. Riesgos y consideraciones

- **No alterar catálogo/stock**: los cambios de atributos cosméticos son
  metadatos, no afectan stock ni precios. Verificar en cada paso.
- **Sincronización**: al pasar a datos vivos, el `CATALOGO` estático debe
  eliminarse para evitar doble fuente de verdad.
- **Rendimiento**: cachear el catálogo en `localStorage` para no golpear el
  endpoint en cada carga.
- **Privacidad**: el test sigue en `localStorage` (sin datos personales
  obligatorios). El email es opcional. No compartir con terceros.
- **Compatibilidad móvil**: el diseño actual ya es responsive; mantener y
  verificar en tablet/móvil.
- **No romper lo que funciona**: el flujo actual (test → resultado → carrito →
  checkout) debe seguir operativo tras cada fase.

---

## 7. Entregables (mapeo al prompt maestro §9)

| # | Entregable solicitado | Estado |
|---|---|---|
| 1 | Diagnóstico del estado actual | ✅ Este documento (§2, §3) |
| 2 | Propuesta de arquitectura económica | ✅ §4 |
| 3 | Lista de cambios necesarios | ✅ §5 |
| 4 | Test Digital funcional | ✅ Fases 3-4 (`diagnostico.html`) |
| 5 | Sistema de evaluación y puntuaciones | ✅ Fase 3 (`calcularIndicadores`) |
| 6 | Sistema de recomendaciones conectado al catálogo | ✅ Fases 2, 5, 6 (endpoint + carrito) |
| 7 | Rutinas básica y avanzada | ✅ Fases 3, 5 (`armarRutinaBasica/Avanzada`) |
| 8 | Diseño visual adaptado a Clinical Skin | ✅ Fase 4 (marfil/dorado/cobre) |
| 9 | Adaptación a móvil | ✅ Fase 4 (responsive verificado) |
| 10 | Preparación IA fotográfica futura | ✅ Fase 7 (`analisisVisual`) |
| 11 | Documentación de funcionamiento | ✅ Fase 8 (`docs/test-piel-funcionamiento.md`) |
| 12 | Listado de plugins/servicios/costos | ✅ Fase 8 (`docs/plugins-servicios-costos.md`) |

**Todos los entregables del prompt maestro están completados.**
