# Clinical Skin — WordPress + WooCommerce

Sitio de dermocosmética con tienda, diagnóstico de piel interactivo, rutina personalizada, carrito, checkout y envíos. El tema `clinicalskin` sirve como puente entre el sitio estático original (montado en `/static`) y WordPress/WooCommerce.

## Estructura principal

- `theme/clinicalskin/` — tema puente de WordPress.
- `theme/clinicalskin/css/woo.css` — estilos de tienda, ficha, carrito, checkout, confirmación y correo.
- `theme/clinicalskin/functions.php` — lógica principal, catálogo, filtros, correos, estado “Enviado”.
- `theme/clinicalskin/pagos.php` — métodos de pago (Webpay, Khipu, Mercado Pago).
- `theme/clinicalskin/envios.php` — lógica de envíos.
- `theme/clinicalskin/woocommerce/` — plantillas sobrescritas.
- `theme/clinicalskin/woocommerce/emails/` — plantillas HTML y de texto para correos transaccionales.
- `theme/clinicalskin/includes/` — clase de correo personalizada para pedidos enviados.
- `scripts/exportar-hosting.ps1` — exporta `base.sql`, `site.tar.gz` y `wordpress.zip`.
- `docs/subir-hosting.md` — guía de publicación en cPanel sin SSH.
- `docs/diagnostico-test-piel.md` — diagnóstico y plan del Test Digital de Piel (fases 0-8).
- `docs/test-piel-funcionamiento.md` — documentación técnica y funcional del test.
- `docs/plugins-servicios-costos.md` — plugins, servicios y costos recurrentes.
- `docs/logica-diagnostico.md` (en el sitio estático) — reglas del motor del test.

## Sitio estático original

La carpeta `C:\xampp\htdocs\clinicalskin` se monta como `/static` en el contenedor y como raíz del hosting. Contiene HTML, CSS, JS e imágenes del diseño original.

## Funciones clave

- Catálogo de 42 productos reales con stock inicial 1.
- Filtros por marca y tipo, con reset visible.
- **Test Digital de Piel** (6 preguntas): 5 tipos de piel, 11 preocupaciones con
  selección múltiple, hábitos, indicadores documentados, rutinas básica y
  avanzada, catálogo en tiempo real y WhatsApp.
- Rutina completa con descuento automático al añadir 2 o más productos.
- Correos transaccionales con identidad visual de Clinical Skin.
- Estado manual de pedido **Enviado** y seguimiento en correos.
- Fondo mármol/floral en fichas, tienda, carrito, checkout, confirmación, contacto, filosofía, diagnóstico y resultado.

## Convenciones

- Los cambios de diseño se concentran en `css/woo.css` y `functions.php`.
- Las imágenes optimizadas para correo están en `img/` (`logo-email.jpg`, `email-header-clinicalskin.jpg`, `email-footer-clinicalskin.jpg`, `email-signature-clinicalskin.png`, `email-icons/`, `producto-fondo.jpg`).
- No se deben alterar el catálogo ni el stock.
- No se deben regenerar paquetes ni la base de datos salvo que se solicite.
- Las comprobaciones deben ser focalizadas.

## Pendientes

- Activar cobros reales con Webpay y Chilexpress.
- Cotización real de BlueExpress.
- SMTP y cuenta de correo real para remitente.
- Actualización manual de estados de pedido.

## Publicación

La guía actualizada está en `docs/subir-hosting.md`. El exportador genera los paquetes en `salida/`.