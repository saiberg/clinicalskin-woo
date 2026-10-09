<?php
/**
 * Un método de pago con el aspecto de las tarjetas de opción del diseño
 * (checkout.html): chip WEBPAY/KHIPU/MP, título y subtítulo, y debajo la
 * caja con los campos de la tarjeta (solo para el método elegido).
 *
 * Se conservan name="payment_method", id="payment_method_<id>" y
 * .payment_box, que es lo que usa el JS de WooCommerce del checkout.
 *
 * @package ClinicalSkin
 */

defined( 'ABSPATH' ) || exit;

$cs_chips = apply_filters(
	'cs_pagos_chips',
	array(
		'webpay'  => 'WEBPAY',
		'khipu'   => 'KHIPU',
		'mercado' => 'MP',
	)
);
$cs_id  = $gateway->id;
$cs_chip = isset( $cs_chips[ $cs_id ] ) ? $cs_chips[ $cs_id ] : '';
$cs_subs = apply_filters( 'cs_pagos_subtitulos', array() );
$cs_sub  = isset( $cs_subs[ $cs_id ] )
	? $cs_subs[ $cs_id ]
	: ( method_exists( $gateway, 'cs_subtitulo' ) ? $gateway->cs_subtitulo() : $gateway->get_description() );
/* Nota corta bajo los campos (p. ej. la de redirección a Webpay). */
$cs_nota = (string) apply_filters( 'cs_pagos_nota', '', $cs_id );
/* La caja se pinta si hay campos, descripción o nota; .card-fields solo si
   algo de ahí necesita el borde punteado superior del diseño. */
$cs_caja = $gateway->has_fields() || $gateway->get_description() || '' !== $cs_nota;
$cs_card = ( $gateway->has_fields() || '' !== $cs_nota ) ? ' card-fields' : '';
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?><?php echo $gateway->chosen ? ' active' : ''; ?>">
	<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />

	<span class="cs-pay-row">
		<?php if ( $cs_chip ) : ?>
		<span class="pay-logo"><?php echo esc_html( $cs_chip ); ?></span>
		<?php endif; ?>
		<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>" class="rc-copy">
			<strong><?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></strong>
			<?php if ( $cs_sub ) : ?>
			<span><?php echo esc_html( $cs_sub ); ?></span>
			<?php endif; ?>
		</label>
	</span>

	<?php if ( $cs_caja ) : ?>
		<?php /* .card-fields solo en el método con campos (o nota) del diseño. */ ?>
		<div class="payment_box payment_method_<?php echo esc_attr( $cs_id ); ?><?php echo esc_attr( $cs_card ); ?><?php echo $gateway->chosen ? ' visible' : ''; ?>" <?php if ( ! $gateway->chosen ) : ?>style="display:none;"<?php endif; ?>>
			<?php $gateway->payment_fields(); ?>
			<?php if ( '' !== $cs_nota ) : ?>
				<p class="secure-note">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="11" width="16" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
					<?php echo esc_html( $cs_nota ); ?>
				</p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</li>
