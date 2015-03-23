<?php
/*
 * Plugin Name: Ziftr for WooCommerce
 * Plugin URI: http://www.ziftr.com/
 * Description: Bring Ziftr platform and ziftrPAY functionality to WooCommerce
 * Author: Ziftr and contributors
 * Author URI: http://www.ziftr.com
 * Version: 0.1.0
 * 
 * Copyright: © 2014-2015 Ziftr, LLC
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//check if woocommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if( !class_exists( 'WC_Ziftr' ) ){
		class WC_Ziftr extends WC_Settings_API
		{

			public function __construct()
			{
				// this is called before the checkout form submit
				add_action( 'woocommerce_checkout_before_customer_details',array( $this,'woocommerce_checkout_before_customer_details' ) );

				// adding ziftr checkout along with  regular woocommerce checkout
				add_filter( 'woocommerce_proceed_to_checkout', array( $this,'add_ziftr_checkout_after_reqular_checkout' ) );

				// Define user set variables
				$this->title               = $this->get_option( 'title' );
				$this->description         = $this->get_option( 'description' );
				$this->show_above_checkout = 'yes' === $this->get_option( 'show_above_checkout', 'yes' );
				$this->show_on_cart        = 'yes' === $this->get_option( 'show_on_cart', 'yes' );
				$this->sandbox             = 'yes' === $this->get_option( 'api_sandbox', 'no' );
				$this->publishable_key     = $this->get_option( 'api_publishable_key' );
				$this->private_key         = $this->get_option( 'api_private_key' );

				add_filter( 'woocommerce_payment_gateways', array( $this,'add_ziftrpay' ) );

			}

			/**
			 * Add ZiftrPAY as a gateway
			 */
			function add_ziftrpay( $methods ) {
				include('includes/class-wc-gateway-ziftrpay.php');
				$methods[] = 'WC_Ziftrpay_Gateway'; 
				return $methods;
			}

			/**
			 * Logging method
			 * @param  string $message
			 */
			public function log( $message ) {
				if ( $this->debug ) {
					if ( empty( $this->log ) ) {
						$this->log = new WC_Logger();
					}
					$this->log->add( 'ziftrpay', $message );
				}
			}

			/**
			 * Do anything on the before the checkout form
			 **/
			public function woocommerce_checkout_before_customer_details( $product ){
				if ( $this->show_above_checkout ) {
					echo '<div class="woocommerce-info ziftrpay-info">Have a ZiftrPAY account? Use your saved details and skip the line <a href="#">Click here to checkout with ZiftrPAY</a></div>';
				}
			}

			public function add_ziftr_checkout_after_reqular_checkout(){
				if ( $this->show_on_cart ) {
					echo '<a href="#" class="checkout-button button alt wc-forward">Checkout using ZiftrPAY</a>';
				}
			}

		}

		/**
		 * instantiating Class
		 **/
		$GLOBALS['wc_ziftr'] = new WC_Ziftr();
		

	}//END if ( !class_exists( WC_Ziftr ) )
}
