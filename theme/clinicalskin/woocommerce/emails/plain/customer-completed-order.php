<?php
/** Plain-text version of the customer completed order email. */

defined( 'ABSPATH' ) || exit;

echo wp_strip_all_tags( $email_heading ) . "\n\n";
cs_email_order_progress( $order, $sent_to_admin, true, $email );
printf( "Hola %s,\n\n", $order->get_billing_first_name() ? $order->get_billing_first_name() : '' );
echo "¡Tu pedido figura como entregado!\n";
echo "Gracias por comprar en Clinical Skin. Esperamos que disfrutes tus productos.\n\n";
printf( "Número de pedido: #%s\n", $order->get_order_number() );
printf( "Fecha: %s\n\n", wc_format_datetime( $order->get_date_created() ) );

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
