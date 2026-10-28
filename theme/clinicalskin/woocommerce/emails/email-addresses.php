<?php
/** Shipping address and shipping method cards for Clinical Skin emails. */

defined( 'ABSPATH' ) || exit;

$shipping_address = $order->get_formatted_shipping_address();
$billing_address  = $order->get_formatted_billing_address();
$address          = $shipping_address ? $shipping_address : $billing_address;
$shipping_phone   = $order->get_shipping_phone() ? $order->get_shipping_phone() : $order->get_billing_phone();
$shipping_method  = $order->get_shipping_method();
$billing_plain    = strtolower( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $billing_address ) ) );
$shipping_plain   = strtolower( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $shipping_address ) ) );
$different_bill   = $billing_address && $shipping_address && $billing_plain !== $shipping_plain;

if ( ! $address && ! $shipping_method ) {
	return;
}
?>
<table id="addresses" role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;margin:22px 0 8px;border-collapse:separate;border-spacing:10px 0">
	<tr>
		<td class="cs-address-column" width="50%" valign="top" style="width:50%;padding:0 5px 0 0">
			<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border:1px solid #eee3d8;border-radius:9px;background:#fffdfa">
				<tr><td style="padding:15px 16px 6px;color:#633e2c;font-family:Georgia,serif;font-size:17px">Dirección de envío</td></tr>
				<tr><td style="padding:0 16px 16px;color:#66574c;font-family:Arial,sans-serif;font-size:13px;line-height:1.55">
					<address class="cs-shipping-address" style="margin:0;color:#66574c;font-style:normal">
						<?php echo wp_kses_post( $address ? $address : esc_html__( 'Dirección no informada', 'woocommerce' ) ); ?>
						<?php if ( $shipping_phone ) : ?><br /><?php echo esc_html( $shipping_phone ); ?><?php endif; ?>
					</address>
				</td></tr>
			</table>
		</td>
		<td class="cs-address-column" width="50%" valign="top" style="width:50%;padding:0 0 0 5px">
			<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;border:1px solid #eee3d8;border-radius:9px;background:#fffdfa">
				<tr><td style="padding:13px 16px 5px">
					<table role="presentation" cellspacing="0" cellpadding="0" border="0"><tr>
						<td width="42" valign="middle" style="width:42px;padding:0"><img src="<?php echo esc_url( home_url( '/img/email-icons/step-truck-idle.png' ) ); ?>" width="36" height="36" alt="" style="display:block;width:36px;height:36px;max-width:36px;border:0" /></td>
						<td valign="middle" style="padding:0 0 0 8px;color:#633e2c;font-family:Georgia,serif;font-size:17px">Método de envío</td>
					</tr></table>
				</td></tr>
				<tr><td style="padding:0 16px 16px;color:#66574c;font-family:Arial,sans-serif;font-size:13px;line-height:1.55"><?php echo esc_html( $shipping_method ? $shipping_method : 'Despacho por coordinar' ); ?></td></tr>
			</table>
		</td>
	</tr>
	<?php if ( $different_bill ) : ?>
	<tr><td colspan="2" style="padding:12px 5px 0;color:#76675b;font-family:Arial,sans-serif;font-size:12px;line-height:1.5"><strong>Dirección de facturación:</strong> <?php echo wp_kses_post( $billing_address ); ?></td></tr>
	<?php endif; ?>
</table>
