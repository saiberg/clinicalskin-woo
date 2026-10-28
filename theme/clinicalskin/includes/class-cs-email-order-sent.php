<?php
/**
 * Email to customers when an order is manually marked as sent.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'CS_Email_Order_Sent' ) && class_exists( 'WC_Email' ) ) {
	class CS_Email_Order_Sent extends WC_Email {
		public function __construct() {
			$this->id             = 'customer_enviado_order';
			$this->customer_email = true;
			$this->title          = 'Pedido enviado';
			$this->description   = 'Aviso al cliente cuando el pedido se marca manualmente como enviado.';
			$this->email_group    = 'order-updates';
			$this->template_html  = 'emails/customer-enviado-order.php';
			$this->template_plain = 'emails/plain/customer-enviado-order.php';
			$this->placeholders   = array(
				'{order_date}'   => '',
				'{order_number}' => '',
			);

			parent::__construct();

			add_action( 'woocommerce_order_status_enviado', array( $this, 'trigger' ), 10, 2 );
		}

		public function get_default_subject() {
			return 'Tu pedido #{order_number} de {site_title} fue enviado';
		}

		public function get_default_heading() {
			return 'Tu pedido va en camino';
		}

		public function get_default_additional_content() {
			return 'Si tienes alguna consulta sobre tu pedido, escríbenos a contacto@clinicalskin.cl.';
		}

		public function trigger( $order_id, $order = false ) {
			$this->setup_locale();

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( is_a( $order, 'WC_Order' ) ) {
				$this->object                         = $order;
				$this->recipient                      = $order->get_billing_email();
				$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
				$this->placeholders['{order_number}'] = $order->get_order_number();
			}

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				)
			);
		}

		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'email'              => $this,
				)
			);
		}
	}
}
