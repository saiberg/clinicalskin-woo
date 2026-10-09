#!/bin/sh
# Segunda parte del diagnóstico: cómo WooCommerce inyecta el loop en un tema
# "no soportado" y quién arma la consulta de productos. Uso: sh /tmp/cs/diag2.sh
W=/var/www/html/wp-content/plugins/woocommerce
echo "== content filter =="
sed -n '295,340p' $W/includes/class-wc-template-loader.php
echo "== llamadas a product_query =="
grep -rn "product_query(" $W/includes/ $W/src/ 2>/dev/null | grep -v 'function product_query' | head -10
echo "== unsupported_theme_shop_content_filter =="
grep -n 'unsupported_theme_shop_content_filter' -A 22 $W/includes/class-wc-template-loader.php | sed -n '25,60p'
