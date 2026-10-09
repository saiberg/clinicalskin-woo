<?php
/**
 * Dirección alternativa (si aplica) y tarjeta "Referencia de entrega" —
 * el checkout.html agrupa la referencia como último campo de dirección.
 *
 * @package ClinicalSkin
 * @var WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-shipping-fields">
	<?php
	/*
	 * El diseño no ofrece "enviar a otra dirección": la compra se despacha
	 * a la dirección de facturación. El filtro permite reactivarlo si la
	 * tienda pasa a gestionar dos direcciones.
	 */
	if ( apply_filters( 'cs_permitir_direccion_envio', false ) && true === WC()->cart->needs_shipping_address() ) :
		?>

		<h3 id="ship-to-different-address">
			<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" /> <span>Enviar a otra dirección</span>
			</label>
		</h3>

		<div class="shipping_address">

			<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

			<div class="woocommerce-shipping-fields__field-wrapper">
				<?php
				$fields = $checkout->get_checkout_fields( 'shipping' );

				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
				?>
			</div>

			<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

		</div>

	<?php endif; ?>
</div>

<?php if ( $checkout->get_checkout_fields( 'order' ) ) : ?>
<div class="woocommerce-additional-fields">
	<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

	<div class="form-card" id="cs-card-referencia">
		<h2>Referencia de entrega</h2>
		<p class="fc-sub">Algo que nos ayude a encontrarte: torre, piso, portón o una persona de contacto.</p>
		<div class="field-grid">
			<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
				<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>

	<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
</div>
<?php endif; ?>
