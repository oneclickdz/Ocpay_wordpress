/**
 * OCPay Block Payment Method
 *
 * Registers OCPay as a payment method for WooCommerce Blocks Checkout
 */

import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { __ } from '@wordpress/i18n';

const settings = window.ocpayBlocksData || {
    gatewayId: 'ocpay',
    title: 'OCPay - OneClick Payment',
    description: 'Pay securely using OCPay - powered by SATIM bank-grade security.',
    icon: '',
    canMakePayment: true,
};

registerPaymentMethod( {
    name: settings.gatewayId,
    label: settings.title,
    content: () => <div>{settings.description}</div>,
    edit: () => <div>{settings.description}</div>,
    canMakePayment: () => settings.canMakePayment,
    ariaLabel: settings.title,
    supports: {
        features: [ 'products' ],
    },
} );

console.log( 'OCPay payment method registered for blocks checkout' );
