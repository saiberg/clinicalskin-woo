<?php
/**
 * Texto de privacidad del checkout en español (igual al del diseño).
 * Uso: wp eval-file /tmp/cs/texto-privacidad.php --user=1
 */

$texto = 'Al confirmar aceptas la Política de privacidad de Clinical Skin.';

update_option( 'woocommerce_checkout_privacy_policy_text', $texto );

echo 'guardado=' . get_option( 'woocommerce_checkout_privacy_policy_text' ) . "\n";
echo 'pagina_privacidad=' . get_option( 'wp_page_for_privacy_policy' ) . "\n";
echo 'pagina_terminos=' . get_option( 'woocommerce_terms_page_id' ) . "\n";
