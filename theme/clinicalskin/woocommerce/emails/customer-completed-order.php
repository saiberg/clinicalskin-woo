<?php
/** Customer email when the order is marked as delivered/completed. */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>
<p class="email-brand-subtitle">TU CONFIANZA NOS INSPIRA</p>
<?php cs_email_order_progress( $order, $sent_to_admin, $plain_text, $email ); ?>
<div class="email-introduction">
	<p style="margin:0 0 12px;color:#493e35;font-size:17px"><?php echo $order->get_billing_first_name() ? 'Hola ' . esc_html( $order->get_billing_first_name() ) . ',' : 'Hola,'; ?></p>
	<p style="margin:0 0 10px">¡Tu pedido figura como entregado!</p>
	<p style="margin:0">Gracias por comprar en Clinical Skin. Esperamos que disfrutes tus productos y que acompañen muy bien tu rutina.</p>
</div>
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:4px 0 18px"><tr><td align="right" style="padding:0"><img src="<?php echo esc_url( home_url( '/img/email-signature-clinicalskin.png' ) ); ?>" width="180" height="133" alt="Gracias por ser parte de Clinical Skin" style="display:block;width:180px;height:auto;max-width:100%;margin:0 0 0 auto;padding:0;border:0" /></td></tr></table>

<?php
cs_email_order_summary_open( $order );
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
cs_email_order_summary_close();
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
cs_email_brand_trust_strip();

do_action( 'woocommerce_email_footer', $email );
