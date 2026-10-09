<?php
/**
 * Pedido recibido con el formato de confirmacion.html del diseño estático:
 * icono, eyebrow con el número de pedido, resumen de 3 tarjetas, "¿Qué
 * sigue?" y las dos acciones.
 *
 * @package ClinicalSkin
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-order">

	<?php if ( $order && $order->has_status( 'failed' ) ) : ?>

		<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'No pudimos procesar este pedido. No se realizó ningún cobro.', 'clinicalskin' ); ?></p>
		<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Volver a intentar el pago', 'clinicalskin' ); ?></a>
		</p>

	<?php elseif ( $order ) : ?>

		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<section class="section-tight">
			<div class="container">
				<div class="confirm-wrap">
					<div class="confirm-icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
					</div>
					<span class="eyebrow"><?php echo esc_html( sprintf( __( 'Compra confirmada · Pedido #CS-%s', 'clinicalskin' ), $order->get_order_number() ) ); ?></span>
					<h1><?php esc_html_e( '¡Gracias por confiar en tu piel!', 'clinicalskin' ); ?></h1>
					<p><?php esc_html_e( 'Recibimos tu pedido y te enviamos el comprobante a tu correo. Preparamos tu rutina con el mismo cuidado con que la elegimos para ti.', 'clinicalskin' ); ?></p>

					<div class="summary-grid three">
						<div class="summary-card">
							<span><?php esc_html_e( 'Total pagado', 'clinicalskin' ); ?></span>
							<strong><?php echo wp_strip_all_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ); ?></strong>
						</div>
						<div class="summary-card">
							<span><?php esc_html_e( 'Despacho', 'clinicalskin' ); ?></span>
							<strong><?php echo esc_html( cs_despacho_texto( $order ) ); ?></strong>
						</div>
						<div class="summary-card">
							<span><?php esc_html_e( 'Entrega estimada', 'clinicalskin' ); ?></span>
							<strong><?php echo esc_html( cs_entrega_texto( $order ) ); ?></strong>
						</div>
					</div>

					<?php
					/* Detalle de productos con las filas .os-item del resumen del diseño. */
					$cs_items = $order->get_items();
					if ( $cs_items ) :
						?>
						<div class="cs-detalles">
							<h3 class="ship-subhead"><?php esc_html_e( 'Detalle del pedido', 'clinicalskin' ); ?></h3>
							<div class="os-list">
								<?php foreach ( $cs_items as $cs_item ) : ?>
									<?php $cs_producto = $cs_item->get_product(); ?>
									<div class="os-item">
										<div class="os-thumb"><?php echo $cs_producto ? wp_kses_post( $cs_producto->get_image() ) : ''; ?></div>
										<div class="os-copy">
											<strong><?php echo esc_html( $cs_item->get_name() ); ?></strong>
											<span><?php echo esc_html( sprintf( __( 'Cant. %s', 'clinicalskin' ), $cs_item->get_quantity() ) ); ?></span>
										</div>
										<span class="os-price"><?php echo wp_strip_all_tags( wc_price( $cs_item->get_total(), array( 'currency' => $order->get_currency() ) ) ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php
					endif;
					?>

					<div class="tip-box">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
						<p><strong><?php esc_html_e( '¿Qué sigue?', 'clinicalskin' ); ?></strong> <?php esc_html_e( 'Preparamos tu pedido, te avisamos cuando salga a reparto y podrás seguirlo con el número de pedido. Si tienes dudas sobre cómo usar tu rutina, escríbenos: estamos para ayudarte.', 'clinicalskin' ); ?></p>
					</div>

					<div class="actions-row">
						<a href="<?php echo esc_url( home_url( '/resultado.html' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Ver mi rutina', 'clinicalskin' ); ?></a>
						<a href="<?php echo esc_url( home_url( '/productos.html' ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Seguir explorando', 'clinicalskin' ); ?></a>
					</div>
				</div>
			</div>
		</section>

		<?php
		/* Datos de transferencia y avisos del gateway (si los hay). */
		do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
		do_action( 'woocommerce_thankyou', $order->get_id() );
		?>

	<?php else : ?>

		<p class="woocommerce-notice"><?php esc_html_e( 'No encontramos este pedido. Si tienes dudas, escríbenos y te ayudamos.', 'clinicalskin' ); ?></p>

	<?php endif; ?>

</div>
