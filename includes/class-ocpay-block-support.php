<?php
/**
 * OCPay Block Payment Support
 *
 * @package OCPay_WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * OCPay_Block_Support class
 *
 * Adds support for WooCommerce Blocks Checkout
 */
class OCPay_Block_Support {

	/**
	 * Initialize block support
	 *
	 * @return void
	 */
	public static function init() {
		// Register block script
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_block_scripts' ), 100 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_block_scripts' ), 100 );
		
		// Filter available payment gateways for REST API (used by Blocks)
		add_filter( 'woocommerce_rest_payment_gateways_controller_get_items_permissions_check', array( __CLASS__, 'allow_blocks_payment_methods' ), 10, 3 );
	}

	/**
	 * Enqueue block payment method script
	 *
	 * @return void
	 */
	public static function enqueue_block_scripts() {
		// Only load on checkout
		if ( ! ( is_checkout() || is_cart() ) && ! function_exists( 'is_block_content' ) ) {
			return;
		}

		// Register the payment method script for blocks
		wp_register_script(
			'ocpay-blocks-payment-method',
			OCPAY_WOOCOMMERCE_URL . 'assets/js/blocks-payment-method.js',
			array(
				'wc-blocks-checkout',
				'wc-blocks-data-store',
			),
			OCPAY_WOOCOMMERCE_VERSION,
			true
		);

		// Check if this is a block-based checkout
		if ( has_block( 'woocommerce/checkout' ) || has_block( 'woocommerce/cart' ) ) {
			wp_enqueue_script( 'ocpay-blocks-payment-method' );

			// Pass gateway info to JavaScript
			wp_localize_script(
				'ocpay-blocks-payment-method',
				'ocpayBlocksData',
				array(
					'gatewayId'     => 'ocpay',
					'title'         => esc_html__( 'OCPay - OneClick Payment', 'ocpay-woocommerce' ),
					'description'   => esc_html__( 'Pay securely using OCPay - powered by SATIM bank-grade security.', 'ocpay-woocommerce' ),
					'icon'          => OCPAY_WOOCOMMERCE_URL . 'assets/images/ocpay-logo.png',
					'canMakePayment' => true,
				)
			);
		}
	}

	/**
	 * Allow payment methods to be retrieved via REST API
	 *
	 * @param bool|\WP_Error $permitted Whether the request is permitted.
	 * @param object         $post       The post object.
	 * @param \WP_REST_Request $request The request object.
	 * @return bool|\WP_Error
	 */
	public static function allow_blocks_payment_methods( $permitted, $post = null, $request = null ) {
		// Allow unauthenticated access to payment methods for blocks checkout
		if ( is_wp_error( $permitted ) ) {
			return true; // Override permission check for REST API
		}
		return $permitted;
	}
}

// Initialize block support on plugins_loaded
add_action( 'woocommerce_blocks_loaded', array( 'OCPay_Block_Support', 'init' ), 10 );
