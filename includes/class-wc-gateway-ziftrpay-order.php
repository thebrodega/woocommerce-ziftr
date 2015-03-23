<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Creates an order and populates it with the correct data.
 */
class WC_Gateway_Ziftrpay_Order {

	/**
	 * Gets or creates a ZiftrPAY order
	 * @return string The URL of the cart
	 */
	private function get_checkout_url( $wc_order, $sandbox=false ) {
		return 'http://www.ziftrpay.com/';
	}
}
