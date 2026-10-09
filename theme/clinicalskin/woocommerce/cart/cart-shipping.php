<?php
/**
 * Fila de envío del resumen de la derecha: solo la etiqueta y el costo,
 * como el .summary-row del diseño.
 *
 * Los métodos se eligen en la tarjeta "Dirección de envío"
 * (cs_metodos_envio_html), así que aquí no se imprimen radios: si se
 * hiciera, los inputs duplicados compartirían el nombre shipping_method[n].
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.8.0
 * @var array  $available_methods
 * @var string $chosen_method
 * @var string $package_name
 */

defined( 'ABSPATH' ) || exit;

$elegido = null;

if ( ! empty( $available_methods ) && is_array( $available_methods ) ) {
	foreach ( $available_methods as $metodo ) {
		if ( $metodo->get_id() === $chosen_method ) {
			$elegido = $metodo;
			break;
		}
	}
	if ( ! $elegido ) {
		$elegido = reset( $available_methods );
	}
}

$costo = $elegido ? (float) $elegido->get_cost() : 0.0;
$etiqueta = $package_name ? $package_name : __( 'Envío', 'clinicalskin' );
?>
<tr class="woocommerce-shipping-totals shipping">
	<th><?php echo wp_kses_post( $etiqueta ); ?></th>
	<td data-title="<?php echo esc_attr( $etiqueta ); ?>">
		<?php if ( $elegido ) : ?>
			<?php echo $costo > 0 ? wp_kses_post( wc_price( $costo ) ) : esc_html__( 'Gratis', 'clinicalskin' ); ?>
		<?php else : ?>
			<span class="woocommerce-shipping-unavailable">&mdash;</span>
		<?php endif; ?>
	</td>
</tr>
