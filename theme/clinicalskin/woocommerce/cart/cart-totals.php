<?php
/**
 * Resumen del pedido con el formato de .order-box de carrito.html.
 *
 * Sigue envolviendo todo en <div class="cart_totals"> porque el JS de
 * WooCommerce reemplaza ese nodo tras cada refresco de totales.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

$envio = (float) WC()->cart->get_shipping_total() + (float) WC()->cart->get_shipping_tax();
?>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<div class="summary-row">
		<span><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
		<strong><?php wc_cart_totals_subtotal_html(); ?></strong>
	</div>

	<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
	<div class="summary-row discount">
		<span><?php echo esc_html( wc_cart_totals_coupon_label( $coupon ) ); ?></span>
		<strong><?php wc_cart_totals_coupon_html( $coupon ); ?></strong>
	</div>
	<?php endforeach; ?>

	<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
	<div class="summary-row">
		<span><?php echo esc_html( $fee->name ); ?></span>
		<strong><?php wc_cart_totals_fee_html( $fee ); ?></strong>
	</div>
	<?php endforeach; ?>

	<div class="summary-row">
		<span><?php esc_html_e( 'Envío estándar', 'clinicalskin' ); ?></span>
		<strong><?php echo $envio > 0 ? wc_price( $envio ) : esc_html__( 'Gratis', 'clinicalskin' ); ?></strong>
	</div>

	<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() && WC()->cart->get_total_tax() > 0 ) : ?>
	<div class="summary-row">
		<span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
		<strong><?php wc_cart_totals_taxes_total_html(); ?></strong>
	</div>
	<?php endif; ?>

	<?php if ( wc_coupons_enabled() ) : ?>
	<div class="coupon coupon-row">
		<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Cupón:', 'woocommerce' ); ?></label>
		<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Código de descuento', 'clinicalskin' ); ?>" autocomplete="off" />
		<button type="submit" class="btn btn-ghost btn-sm" name="apply_coupon" value="<?php esc_attr_e( 'Aplicar', 'clinicalskin' ); ?>"><?php esc_html_e( 'Aplicar', 'clinicalskin' ); ?></button>
		<?php do_action( 'woocommerce_cart_coupon' ); ?>
	</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

	<div class="summary-row total">
		<span><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
		<?php wc_cart_totals_order_total_html(); ?>
	</div>

	<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>
</div>
