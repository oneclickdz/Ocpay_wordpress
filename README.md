# OCPay WooCommerce Gateway

A simple WooCommerce payment gateway for OCPay.

## Description

Accept secure payments via OCPay powered by SATIM. This plugin integrates OCPay payment gateway into your WooCommerce store, allowing customers to pay securely using Algerian payment methods.

## Requirements

- PHP >= 7.2
- WooCommerce >= 4.0
- WordPress installation
- OCPay merchant account

## Installation

### Download the Plugin

Download the latest release from the [GitHub releases page](https://github.com/oneclickdz/ocpay_wordpress/releases).

### Install in WordPress

1. Upload the plugin files to the `/wp-content/plugins/ocpay-gateway` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to WooCommerce > Settings > Payments
4. Click on "OCPay" to configure the gateway

## Configuration

### Step 1: Sign Up for OCPay

Before using this plugin, you need to register as a merchant:

1. Visit [OneClickDz](https://oneclickdz.com) to sign up
2. Complete the merchant registration process
3. Wait for your merchant status to be validated
4. Once approved, you'll receive your API credentials

### Step 2: Get Your API Keys

OCPay provides two modes for testing and production:

- **Sandbox Mode**: For testing payments without real transactions
- **Production Mode**: For accepting real payments from customers

You'll need to obtain API keys for each mode from your OCPay merchant dashboard.

### Step 3: Configure the Plugin

1. Go to WooCommerce > Settings > Payments > OCPay
2. Enable the payment gateway
3. Configure the following settings:
   - **Title**: The payment method name shown to customers (e.g., "OCPay")
   - **Description**: Description shown during checkout (e.g., "Pay securely with OCPay")
   - **Environment**: Select "Sandbox" for testing or "Production" for live payments
   - **Sandbox API Key**: Enter your sandbox API key for testing
   - **Production API Key**: Enter your production API key for live payments
   - **Fee Mode**: Choose who pays the transaction fees (Merchant, Customer, or Split)
   - **Final Order Status**: Set the order status after successful payment
4. Save your settings
5. Test a checkout to ensure everything works correctly

### Testing with Sandbox Mode

1. Set "Environment" to "Sandbox" in the plugin settings
2. Enter your sandbox API key in the "Sandbox API Key" field
3. Place test orders to verify the integration
4. Once testing is complete, switch "Environment" to "Production" and use your production API key

## Documentation

For detailed documentation and API references, visit:
- [OCPay Documentation](https://docs.oneclickdz.com/)
- [OneClickDz Website](https://oneclickdz.com)

## Support

For support and questions:
- Visit the [OCPay Documentation](https://docs.oneclickdz.com/)
- Contact OneClickDz support

## Author

OneClickDz - [https://oneclickdz.com](https://oneclickdz.com)

## License

GPL-3.0-or-later
