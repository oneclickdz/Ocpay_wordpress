<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OCPay Payments Blocks integration
 *
 * @version 1.0.0
 * @since 1.0.0
 */
final class WC_Gateway_OCPay_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * The gateway instance.
	 *
	 * @var WC_Gateway_OCPay
	 */
	private $gateway;

	/**
	 * Payment method name/id/slug.
	 *
	 * @var string
	 */
	protected $name = 'ocpay';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_ocpay_settings', [] );
		$this->gateway  = new WC_Gateway_OCPay();
		add_action( 'wp_enqueue_scripts', [ $this, 'ocpay_checkout_styles' ] );
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return $this->gateway->is_available();
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_url        = plugins_url( 'assets/js/frontend/blocks.js', dirname( dirname( dirname( __FILE__ ) ) ) . '/ocpay-gateway.php' );
		$script_asset_path = OCPAY_PLUGIN_DIR . 'assets/js/frontend/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require( $script_asset_path )
			: array(
				'dependencies' => array( 'wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-i18n', 'wp-html-entities' ),
				'version'      => '1.0.0',
			);

		wp_register_script(
			'wc-gateway-ocpay-blocks',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'wc-gateway-ocpay-blocks', 'ocpay-gateway', OCPAY_PLUGIN_DIR . 'languages/' );
		}

		return [ 'wc-gateway-ocpay-blocks' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => $this->get_setting( 'title' ),
			'description' => $this->get_setting( 'description' ),
			'icon'        => plugins_url( 'assets/img/cibeddahabia.png', dirname( dirname( dirname( __FILE__ ) ) ) . '/ocpay-gateway.php' ),
			'supports'    => array_filter( $this->gateway->supports, [ $this->gateway, 'supports' ] ),
		];
	}

	/**
	 * Enqueue a style in the WordPress on frontend.
	 *
	 * @return  void
	 */
	public function ocpay_checkout_styles() {
		wp_enqueue_style( 'ocpay_styles', plugins_url( 'assets/css/style.css', dirname( dirname( dirname( __FILE__ ) ) ) . '/ocpay-gateway.php' ) );
		wp_add_inline_style( 'ocpay_styles', ':root { --ocpay-icon-url: url("' . plugins_url( 'assets/img/cibeddahabia.png', dirname( dirname( dirname( __FILE__ ) ) ) . '/ocpay-gateway.php' ) . '"); }' );
	}
}
