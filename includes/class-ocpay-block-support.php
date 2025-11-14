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
	 * Payment method name
	 *
	 * @var string
	 */
	private $name = 'ocpay';

	/**
	 * Initialize block support
	 *
	 * @return void
	 */
	public static function init() {
		// Enqueue scripts on checkout/cart pages
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_block_scripts' ), 100 );
	}

	/**
	 * Enqueue block payment method script
	 *
	 * @return void
	 */
	public static function enqueue_block_scripts() {
		// Only load on checkout or cart pages with blocks
		global $post;
		if ( ! $post ) {
			return;
		}

		// Check if this page has WooCommerce blocks
		if ( ! ( has_block( 'woocommerce/checkout', $post ) || has_block( 'woocommerce/cart', $post ) ) ) {
			return;
		}

		// Get the asset file for dependencies
		$script_asset_path = OCPAY_WOOCOMMERCE_PATH . 'assets/js/blocks-payment-method.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array(
					'wp-element',
					'wp-i18n',
					'wp-html-entities',
					'wc-blocks-registry',
					'wc-settings'
				),
				'version'      => OCPAY_WOOCOMMERCE_VERSION,
			);

		// Register and enqueue the script
		wp_register_script(
			'ocpay-blocks-integration',
			OCPAY_WOOCOMMERCE_URL . 'assets/js/blocks-payment-method.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_enqueue_script( 'ocpay-blocks-integration' );

		// Get gateway and pass its data to JavaScript
		$gateways = WC()->payment_gateways->payment_gateways();
		$gateway  = isset( $gateways['ocpay'] ) ? $gateways['ocpay'] : null;

		if ( $gateway ) {
			$payment_data = array(
				'title'       => $gateway->get_title(),
				'description' => $gateway->get_description(),
				'supports'    => array_filter( $gateway->supports, array( $gateway, 'supports' ) ),
				'logo_url'    => OCPAY_WOOCOMMERCE_URL . 'assets/images/ocpay-logo.png',
			);
		} else {
			$payment_data = array(
				'title'       => __( 'OCPay - OneClick Payment', 'ocpay-woocommerce' ),
				'description' => __( 'Pay securely using OCPay - powered by SATIM bank-grade security.', 'ocpay-woocommerce' ),
				'logo_url'    => OCPAY_WOOCOMMERCE_URL . 'assets/images/ocpay-logo.png',
			);
		}

		// Localize script with payment data
		wp_localize_script(
			'ocpay-blocks-integration',
			'ocpayBlocksData',
			$payment_data
		);

		// Also try to set it via wc.wcSettings if available
		wp_add_inline_script(
			'ocpay-blocks-integration',
			sprintf(
				'if (window.wc && window.wc.wcSettings) { window.wc.wcSettings.setSetting("ocpay_data", %s); }',
				wp_json_encode( $payment_data )
			),
			'after'
		);

		error_log( 'OCPay: Blocks payment method script enqueued' );
	}
}
