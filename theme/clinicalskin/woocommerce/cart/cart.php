<?php
/**
 * Carrito con el formato de carrito.html del sitio estático.
 *
 * Mantiene las clases que el JS de WooCommerce necesita para funcionar:
 *  - form.woocommerce-cart-form          envuelve el layout (post en AJAX)
 *  - .woocommerce-cart-form__contents   el panel con los artículos
 *  - .cart_item + input.qty             refresco al cambiar la cantidad
 *  - .product-remove > a                borrado sin recargar
 *  - button[name="update_cart"]         dispara el refresco (js/carrito.js)
 *  - .cart_totals                       caja de resumen (reemplazada por AJAX)
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );
?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<div class="cart-layout">
		<div>
			<div class="cart-panel woocommerce-cart-form__contents">
				<?php do_action( 'woocommerce_before_cart_table' ); ?>
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] < 1 ) {
						continue;
					}

					$permalink         = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					/* Mínimo 1 (para quitar existe el enlace "Eliminar"), no la tabla nativa. */
					$min_quantity      = 1;
					$max_quantity      = $_product->is_sold_individually() ? 1 : $_product->get_max_purchase_quantity();
					$meta              = cs_carrito_meta( $product_id );
					$imagen            = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
					$nombre            = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$subtotal_producto = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
					?>
					<div class="cart-item woocommerce-cart-form__cart-item cart_item" data-price="<?php echo esc_attr( $_product->get_price() ); ?>">
						<div class="cart-thumb"><?php echo $imagen; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>

						<div class="cart-item-info">
							<?php cs_tienda_etiquetas( $product_id ); ?>
							<h2>
								<?php if ( $permalink ) : ?>
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $nombre ); ?></a>
								<?php else : ?>
								<?php echo esc_html( $nombre ); ?>
								<?php endif; ?>
							</h2>
							<?php if ( $meta ) : ?>
							<span class="meta"><?php echo esc_html( $meta ); ?></span>
							<?php endif; ?>

							<div class="qty-selector">
								<button type="button" class="qty-step" data-qty="-1" aria-label="<?php esc_attr_e( 'Quitar una unidad', 'clinicalskin' ); ?>">−</button>
								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput
									'woocommerce_cart_item_quantity',
									woocommerce_quantity_input(
										array(
											'input_name'   => "cart[{$cart_item_key}][qty]",
											'input_id'     => 'qty-' . $cart_item_key,
											'input_value'  => $cart_item['quantity'],
											'max_value'    => $max_quantity,
											'min_value'    => $min_quantity,
											'product_name' => $nombre,
										),
										$_product,
										false
									),
									$cart_item_key,
									$cart_item
								);
								?>
								<button type="button" class="qty-step" data-qty="1" aria-label="<?php esc_attr_e( 'Añadir una unidad', 'clinicalskin' ); ?>">+</button>
							</div>
						</div>

						<div class="cart-item-side">
							<span class="line-price"><?php echo $subtotal_producto; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<div class="product-remove">
								<?php
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput
									'woocommerce_cart_item_remove_link',
									sprintf(
										'<a role="button" href="%s" class="remove-item" aria-label="%s">%s</a>',
										esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
										esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $nombre ) ) ),
										esc_html__( 'Eliminar', 'clinicalskin' )
									),
									$cart_item_key
								);
								?>
							</div>
						</div>
					</div>
					<?php
				}
				?>

				<?php do_action( 'woocommerce_cart_contents' ); ?>
				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</div>

			<div class="cart-panel-actions">
				<button type="submit" class="button cs-update-cart" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			</div>

			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</div>

		<aside class="order-box">
			<h3><?php esc_html_e( 'Resumen del pedido', 'clinicalskin' ); ?></h3>
			<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
			<?php do_action( 'woocommerce_cart_collaterals' ); ?>

			<div class="wc-proceed-to-checkout">
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="checkout-button btn btn-primary btn-block"><?php esc_html_e( 'Finalizar compra', 'clinicalskin' ); ?></a>
			</div>
			<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>" class="btn btn-outline btn-block cs-seguir"><?php esc_html_e( 'Seguir comprando', 'clinicalskin' ); ?></a>

			<div class="trust-row">
				<span><?php esc_html_e( 'Pago seguro: Webpay · Khipu · Mercado Pago', 'clinicalskin' ); ?></span>
				<span><?php esc_html_e( 'Despacho a todo Chile', 'clinicalskin' ); ?></span>
				<span><?php esc_html_e( 'Cambios y devoluciones 30 días', 'clinicalskin' ); ?></span>
			</div>
		</aside>
	</div>
</form>

<?php do_action( 'woocommerce_after_cart' ); ?>
