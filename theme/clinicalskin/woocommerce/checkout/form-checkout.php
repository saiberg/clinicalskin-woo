<?php
/**
 * Checkout con el formato de checkout.html: tarjetas de formulario en la
 * columna izquierda y resumen del pedido en la columna derecha.
 *
 * Se conservan los ids y clases que necesita el JS de WooCommerce:
 * #customer_details, #order_review, .woocommerce-checkout-review-order-table,
 * .woocommerce-checkout-payment y #payment.
 *
 * @package ClinicalSkin
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

/* Sin registro y sin sesión no se puede comprar. */
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'Debes iniciar sesión para finalizar tu compra.', 'woocommerce' ) ) );
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<div class="checkout-layout">

		<div class="checkout-col">

			<?php if ( $checkout->get_checkout_fields() ) : ?>
			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<div id="customer_details">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
			<?php endif; ?>

			<div class="form-card" id="cs-card-pago">
				<h2>Pago</h2>
				<p class="fc-sub">Transacciones cifradas. Clinical Skin nunca almacena los datos de tu tarjeta.</p>
				<?php woocommerce_checkout_payment(); ?>
			</div>

		</div>

		<aside class="order-box">
			<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
			<h3 id="order_review_heading">Tu pedido</h3>

			<div class="os-list">
				<?php cs_resumen_pedido(); ?>
			</div>

			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>
			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

			<div class="cs-pagar" data-cs-pagar></div>

			<div class="trust-row">
				<span>Pago cifrado SSL</span>
				<span>Transferencia · Contra entrega</span>
				<span>Cambios y devoluciones 30 días</span>
			</div>
		</aside>

	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
