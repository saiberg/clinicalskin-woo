# Parches de plugins (hechos a mano)

Estos tres archivos NO son los que vienen en wordpress.org: tienen arreglos que
dependen de este sitio. Se guardan acá para no perderlos si se actualiza el plugin
o si se pierde el volumen de Docker.

| Archivo en el contenedor | Arreglo |
|---|---|
| `wp-content/plugins/chilexpress-oficial/public/class-chilexpress-woo-oficial-public.php` | Recarga de comunas al cambiar de región: `wc_enqueue_js` deprecado → `wp_add_inline_script` (handle `chilexpress-checkout-select2`), y guard `if (window.woocommerce_chilexpress) return;` en `state_changing` para no chocar con Starken. |
| `wp-content/plugins/chilexpress-oficial/includes/class-chilexpress-woo-oficial-coverage.php` | `codigo_region_chilexpress()` en `Coverage::obtener_comunas`: traduce el código `CL-XX` que manda WooCommerce al código corto (`RM`, `BIO`, …) que entiende la API, y cachea las comunas (16 regiones pre-cargadas). |
| `wp-content/plugins/calculo-de-despacho-via-starken-para-woocommerce/assets/js/script.js` | Guard `if (window.woocommerce_chilexpress) return;` en `state_changing` para que Starken no reemplace el `select` de comuna que arma Chilexpress. |

## Cómo reaplicarlos

```powershell
$cont = "clinicalskin-woo-wordpress-1"
$p = "C:\docker\clinicalskin-woo\parches"
docker cp "$p\chilexpress-oficial\public\class-chilexpress-woo-oficial-public.php"      ${cont}:/var/www/html/wp-content/plugins/chilexpress-oficial/public/
docker cp "$p\chilexpress-oficial\includes\class-chilexpress-woo-oficial-coverage.php"  ${cont}:/var/www/html/wp-content/plugins/chilexpress-oficial/includes/
docker cp "$p\calculo-de-despacho-via-starken-para-woocommerce\assets\js\script.js"     ${cont}:/var/www/html/wp-content/plugins/calculo-de-despacho-via-starken-para-woocommerce/assets/js/
```

Tras copiar, verificar en el checkout que las comunas carguen al cambiar de región
(biobío → 72 comunas) y que no aparezca el aviso `Deprecated: wc_enqueue_js`.

Para llevar los mismos archivos a un hosting: copiarlos a la carpeta correspondiente
de `wp-content/plugins/` **después** de instalar el plugin (el orden importa: instalar
y luego sobrescribir).
