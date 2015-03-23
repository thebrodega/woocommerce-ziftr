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
		class WC_Ziftr extends WC_Payment_Gateway
		{

			public function __construct()
			{
				// this is called before the checkout form submit
				add_action( 'woocommerce_checkout_before_customer_details',array( $this,'woocommerce_checkout_before_customer_details' ) );

				// adding ziftr checkout along with  regular woocommerce checkout
				add_filter( 'woocommerce_proceed_to_checkout', array( $this,'add_ziftr_checkout_after_reqular_checkout' ) );

				$this->id                 = 'ziftrpay';
				$this->has_fields         = false;
				$this->order_button_text  = __( 'Proceed to ZiftrPAY', 'woocommerce' );
				$this->method_title       = __( 'ZiftrPAY', 'woocommerce' );
				$this->method_description = __( 'ZiftrPAY works by sending customers to ZiftrPAY where they can enter their payment information and pay with credit card or cryptocurrency.', 'woocommerce' );
				$this->supports           = array(
								'products'
							    );

				// Load the settings.
				$this->init_form_fields();
				$this->init_settings();

				// Define user set variables
				$this->title               = $this->get_option( 'title' );
				$this->description         = $this->get_option( 'description' );
				$this->show_above_checkout = 'yes' === $this->get_option( 'show_above_checkout', 'yes' );
				$this->show_on_cart        = 'yes' === $this->get_option( 'show_on_cart', 'yes' );
				$this->sandbox             = 'yes' === $this->get_option( 'api_sandbox', 'no' );
				$this->publishable_key     = $this->get_option( 'api_publishable_key' );
				$this->private_key         = $this->get_option( 'api_private_key' );

				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

				if ( ! $this->is_valid_for_use() ) {
					$this->enabled = 'no';
				}

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
			 * Admin Panel Options
			 * - Options for bits like 'title' and availability on a country-by-country basis
			 *
			 * @since 1.0.0
			 */
			public function admin_options() {
				if ( $this->is_valid_for_use() ) {
				?>
					<h3><?php __( 'ZiftrPAY', 'woocommerce' ); ?></h3>

					<?php if ( empty( $this->publishable_key ) && empty( $this->private_key ) ) : ?>
						<div class="ziftrpay-banner updated">
							<img src="<?php echo plugins_url('/assets/images/admin_logo.png',__FILE__); ?>" />
							<p class="main"><strong><?php _e( 'Getting started', 'woocommerce' ); ?></strong></p>
							<p><?php _e( 'ZiftrPAY is a platform that enabled you to offer your customers more choice by accepting both credit card and cryptocurrency.', 'woocommerce' ); ?></p>

							<p><a href="https://www.ziftrpay.com/merchants/register/" target="_blank" class="button button-primary"><?php _e( 'Sign up for ZiftrPAY', 'woocommerce' ); ?></a> <a href="https://www.ziftrpay.com/" target="_blank" class="button"><?php _e( 'Learn more', 'woocommerce' ); ?></a></p>

						</div>
					<?php else : ?>
						<p><?php _e( 'ZiftrPAY is a platform that enabled you to offer your customers more choice by accepting both credit card and cryptocurrency.', 'woocommerce' ); ?></p>
					<?php endif; ?>
					<table class="form-table">
				<?php
						$this->generate_settings_html();
				?>
					</table>	
				<?php
				} else {
					?>
					<div class="inline error"><p><strong><?php _e( 'Gateway Disabled', 'woocommerce' ); ?></strong>: <?php _e( 'ZiftrPAY does not support your store currency at this time.', 'woocommerce' ); ?></p></div>
					<?php
				}
			}

			/**
			 * Initialise Gateway Settings Form Fields
			 */
			public function init_form_fields() {
				$this->form_fields = include( 'includes/settings-ziftrpay.php' );
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

		/**
		 * Registering Hooks
		 **/
		register_activation_hook( __FILE__, array( 'WC_Ziftr', 'activate' ) );

		register_deactivation_hook( __FILE__, array( 'WC_Ziftr', 'deactivate' ) );

		/**
		 * instntiating Class
		 **/
		$GLOBALS['wc_ziftr'] = new WC_Ziftr();

		function add_ziftrpay( $methods ) {
			$methods[] = 'WC_Ziftr'; 
			return $methods;
		}

		add_filter( 'woocommerce_payment_gateways', 'add_ziftrpay' );

		wp_register_style( 'wc-ziftr-admin', plugins_url( '/assets/css/admin.css', __FILE__ ) );
		wp_enqueue_style( 'wc-ziftr-admin' );
		

	}//END if ( !class_exists( WC_Ziftr ) )
}
