<?php
/** Plain-text version of the customer order confirmation email. */

defined( 'ABSPATH' ) || exit;

echo wp_strip_all_tags( $email_heading ) . "\n\n";
cs_email_order_progress( $order, $sent_to_admin, true, $email );
printf( "Hola %s,\n\n", $order->get_billing_first_name() ? $order->get_billing_first_name() : '' );
echo "¡Gracias por elegir Clinical Skin! Recibimos tu pedido y ya estamos preparándolo con mucho cuidado.\n";
echo "Aquí tienes el resumen de tu compra y el seguimiento de tu pedido.\n\n";
printf( "Número de pedido: #%s\n", $order->get_order_number() );
printf( "Fecha: %s\n\n", wc_format_datetime( $order->get_date_created() ) );

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
