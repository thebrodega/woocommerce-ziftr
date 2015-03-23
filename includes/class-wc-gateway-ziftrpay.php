<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Ziftrpay_Gateway extends WC_Payment_Gateway
{

	public function __construct()
	{
		$this->id                 = 'ziftrpay';
		$this->has_fields         = false;
		$this->order_button_text  = __( 'Proceed to ZiftrPAY', 'woocommerce' );
		$this->method_title       = __( 'ZiftrPAY', 'woocommerce' );
		$this->method_description = __( 'ZiftrPAY works by sending customers to ZiftrPAY where they can enter their payment information and pay with credit card or cryptocurrency.', 'woocommerce' );
		$this->supports           = array(
						'products'
					  );

		$this->title               = $this->get_option( 'title' );
		$this->description         = $this->get_option( 'description' );
		$this->show_above_checkout = 'yes' === $this->get_option( 'show_above_checkout', 'yes' );
		$this->show_on_cart        = 'yes' === $this->get_option( 'show_on_cart', 'yes' );
		$this->sandbox             = 'yes' === $this->get_option( 'api_sandbox', 'no' );
		$this->publishable_key     = $this->get_option( 'api_publishable_key' );
		$this->private_key         = $this->get_option( 'api_private_key' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = 'no';
		}

		wp_register_style( 'wc-ziftr-admin', plugins_url( '/assets/css/admin.css', __FILE__ ) );
		wp_enqueue_style( 'wc-ziftr-admin' );
	}

	/**
	 * get_icon function.
	 *
	 * @return string
	 */
	public function get_icon() {

		$icon = plugins_url('/assets/images/AC_vs_mc_am_dc_zrc_tc_doge_ltc.png',__FILE__);
		$url  = 'https://www.ziftrpay.com/shoppers/';

		$html .= '<img src="' . esc_attr( $icon ) . '" alt="' . __( 'ZiftrPAY accepts credit card and cryptocurrency', 'woocommerce' ) . '" />';

		$html .= sprintf( '<a href="%1$s" class="about_ziftrpay" onclick="javascript:window.open(\'%1$s\',\'WIZiftrpay\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=700\'); return false;" title="' . esc_attr__( 'What is ZiftrPAY?', 'woocommerce' ) . '">' . esc_attr__( 'What is ZiftrPAY?', 'woocommerce' ) . '</a>', esc_url( $url ) );

		return apply_filters( 'woocommerce_gateway_icon', $html, $this->id );
	}

	/**
	 * Check if this gateway is enabled and available in the user's country
	 *
	 * @return bool
	 */
	public function is_valid_for_use() {
		return in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_ziftrpay_supported_currencies', array( 'USD' ) ) );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = include( 'settings-ziftrpay.php' );
	}

	/**
	 * Get the transaction URL.
	 *
	 * @param  WC_Order $order
	 *
	 * @return string
	 */
	public function get_transaction_url( $order ) {
		if ( $this->testmode ) {
			$this->view_transaction_url = 'https://www.sandbox.ziftrpay.com/cgi-bin/webscr?cmd=_view-a-trans&id=%s';
		} else {
			$this->view_transaction_url = 'https://www.ziftrpay.com/cgi-bin/webscr?cmd=_view-a-trans&id=%s';
		}
		return parent::get_transaction_url( $order );
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		include_once( 'includes/class-wc-gateway-ziftrpay-order.php' );

		$order          = wc_get_order( $order_id );

		return array(
				'result'   => 'success',
				'redirect' => $ziftrpay_order->get_checkout_url( $order, $this->sandbox )
			    );
	}
}
