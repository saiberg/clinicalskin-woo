<?php
/**
 * Tarjetas "Contacto" y "Dirección de envío" del checkout.
 *
 * Reparte los campos de facturación en las dos tarjetas de checkout.html
 * (el original los agrupa igual: correo/nombre/apellido/teléfono arriba,
 * dirección debajo). El resto de campos se imprime al final por si surge
 * alguno nuevo, para que nunca se pierda un input del formulario.
 *
 * @package ClinicalSkin
 * @var WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$campos  = $checkout->get_checkout_fields( 'billing' );
$grupos  = array(
	'contacto'  => array( 'billing_email', 'billing_first_name', 'billing_last_name', 'billing_phone' ),
	'direccion' => array( 'billing_country', 'billing_state', 'billing_city', 'billing_address_1', 'billing_address_2' ),
);
$emitidos = array();
?>

<div class="woocommerce-billing-fields">
	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="form-card" id="cs-card-contacto">
		<h2>Contacto</h2>
		<p class="fc-sub">Te enviaremos la confirmación y el seguimiento del pedido por correo.</p>
		<div class="field-grid">
			<?php
			foreach ( $grupos['contacto'] as $clave ) {
				if ( ! isset( $campos[ $clave ] ) ) {
					continue;
				}
				$emitidos[] = $clave;
				woocommerce_form_field( $clave, $campos[ $clave ], $checkout->get_value( $clave ) );
			}
			?>
		</div>
	</div>

	<div class="form-card" id="cs-card-direccion">
		<h2>Dirección de envío</h2>
		<p class="fc-sub">Despachamos a todo Chile. Indica región, comuna y dirección.</p>
		<div class="field-grid cs-direccion-grid">
			<?php
			foreach ( $grupos['direccion'] as $clave ) {
				if ( ! isset( $campos[ $clave ] ) ) {
					continue;
				}
				$emitidos[] = $clave;
				woocommerce_form_field( $clave, $campos[ $clave ], $checkout->get_value( $clave ) );
			}

			/* Campo que no encaje en ningún grupo: no se puede perder. */
			foreach ( $campos as $clave => $campo ) {
				if ( in_array( $clave, $emitidos, true ) ) {
					continue;
				}
				woocommerce_form_field( $clave, $campo, $checkout->get_value( $clave ) );
			}
			?>
		</div>

		<?php
		/* Método de despacho (checkout.html): queda al final de la tarjeta
		 * de dirección y se refresca con los fragmentos del checkout. */
		echo cs_metodos_envio_html();
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>
			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || true === apply_filters( 'woocommerce_create_account_default_checked', false ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Crear una cuenta?', 'woocommerce' ); ?></span>
				</label>
			</p>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>
			<div class="create-account">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $clave => $campo ) : ?>
					<?php woocommerce_form_field( $clave, $campo, $checkout->get_value( $clave ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>
		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
