#!/bin/sh
# Diagnóstico del widget de precio de WooCommerce. Uso: sh /tmp/cs/diag-precio.sh
W=/var/www/html/wp-content/plugins/woocommerce/includes
echo "== the_content =="
grep -rn "add_filter( 'the_content'" $W/ | head -6
echo "== pre_get_posts (inicio) =="
sed -n '327,346p' $W/class-wc-query.php
echo "== get_filtered_price del widget =="
grep -n 'function get_filtered_price' -A 28 $W/widgets/class-wc-widget-price-filter.php | head -36
