# Plugins, Servicios y Costos — Clinical Skin

> Inventario real del stack y sus costos recurrentes.
> Verificado en vivo: 2026-11-13.

---

## 1. Resumen

**Costo nuevo del Test Digital de Piel: $0.** No se instaló ningún plugin ni se
contrató ningún servicio para implementarlo. Todo corre sobre la infraestructura
existente.

---

## 2. Plugins activos (verificados)

| Plugin | Versión | Estado | Costo |
|---|---|---|---|
| WooCommerce | 11.1.2 | Activo | Gratis |
| Transbank Webpay | 1.14.0 | Activo | Gratis* |
| Chilexpress Oficial para WooCommerce | 1.4.5 | Activo | Gratis** |
| Despacho vía Starken Pro para WooCommerce | 2026.07.01 | Activo | Gratis** |

\* El plugin es gratis; Transbank cobra **comisión por transacción** (variable,
según contrato).
\** El plugin es gratis; el transportista cobra **por envío** (según tarifa).

### Tema

| Componente | Versión | Costo |
|---|---|---|
| Tema `clinicalskin` | 1.0 | $0 (desarrollo propio) |

---

## 3. Pasarelas de pago

| Pasarela | Estado | Costo |
|---|---|---|
| Transbank Webpay Plus (REST) | **Activa** | Comisión por transacción |
| Khipu | Registrada, **inactiva** | Comisión (si se activa) |
| Mercado Pago | Registrada, **inactiva** | Comisión (si se activa) |
| Transferencia bancaria (BACS) | Inactiva | $0 |
| Contra entrega (COD) | Inactiva | $0 |
| Cheque | Inactiva | $0 |
| Webpay Oneclick | Inactiva | Comisión (si se activa) |

---

## 4. Métodos de envío (zona "Chile")

| Método | Tipo | Costo |
|---|---|---|
| BlueExpress express | Tarifa plana | Tarifa fija configurada |
| Retiro en punto de atención | Local pickup | $0 |
| Starken a domicilio | Starken Pro | Tarifa por envío |
| Envíos con Chilexpress | Chilexpress Oficial | Tarifa por envío |

---

## 5. Servicios del Test Digital

| Servicio | Estado | Costo |
|---|---|---|
| Motor de reglas (JS) | Implementado | $0 |
| Endpoint catálogo (`cs_catalogo`) | Implementado | $0 |
| Atributos cosméticos en WooCommerce | Implementado | $0 |
| WhatsApp (`wa.me` con mensaje prefijado) | Implementado | $0 |
| Captura de email | Implementado (endpoint vacío) | $0 |
| Análisis fotográfico con IA | **No contratado** | $0 (futuro: variable) |

---

## 6. Dependencias del sistema

| Componente | Versión | Costo |
|---|---|---|
| WordPress | (última) | Gratis |
| MySQL | 8.4 | Gratis |
| Docker (entorno local) | — | Gratis (Docker Desktop según plan) |
| Hosting cPanel (producción) | — | Costo del plan de hosting existente |

---

## 7. Costos recurrentes futuros (opcionales)

| Concepto | Cuándo | Costo estimado |
|---|---|---|
| SMTP / correo transaccional | Al activar envío real de rutinas | Variable (Mailgun, SendGrid, etc.) |
| IA fotográfica | Si se decide activar | Variable (por llamada) |
| Comisión Transbank | Por venta | % por transacción |
| Envíos | Por pedido | Según transportista |

**Ninguno de estos es necesario para que el test funcione hoy.**

---

## 8. Cupones

| Cupón | Uso | Costo |
|---|---|---|
| `rutina` | -10% en rutina del diagnóstico (2+ productos) | Margen comercial |

---

## 9. Estados de pedido

Estándar de WooCommerce + **`wc-enviado`** ("Enviado"), estado manual
personalizado del tema.

---

## 10. Conclusión

El Test Digital de Piel se construyó **sin inversión nueva**. Aprovecha:
- WooCommerce y su catálogo (42 productos).
- El tema propio.
- Los plugins de pago y envío ya instalados.
- `localStorage` para persistencia (sin base de datos extra).
- WhatsApp vía enlace (sin API de pago).

La única inversión futura sería opcional (SMTP, IA fotográfica).
