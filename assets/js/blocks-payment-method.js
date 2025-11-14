/**
 * OCPay Block Payment Method
 *
 * Registers OCPay as a payment method for WooCommerce Blocks Checkout
 */

( function() {
	'use strict';

	const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
	const { createElement } = window.wp.element;
	const { __ } = window.wp.i18n;
	const { decodeEntities } = window.wp.htmlEntities;

	// Get settings from localized data or use defaults
	const settings = window.wc.wcSettings.getSetting( 'ocpay_data', {} );

	// Fallback to legacy data if new format not available
	const legacySettings = window.ocpayBlocksData || {};

	const gatewayData = {
		name: 'ocpay',
		title: settings.title || legacySettings.title || __( 'OCPay - OneClick Payment', 'ocpay-woocommerce' ),
		description: settings.description || legacySettings.description || __( 'Pay securely using OCPay - powered by SATIM bank-grade security.', 'ocpay-woocommerce' ),
		logo_url: settings.logo_url || legacySettings.icon || '',
		supports: settings.supports || { features: [ 'products' ] },
		canMakePayment: legacySettings.canMakePayment !== false,
	};

	// Payment method content component
	const OCPayContent = ( props ) => {
		return createElement( 'div', {
			className: 'wc-block-components-payment-method-content wc-block-components-payment-method-content--ocpay'
		}, [
			gatewayData.description && createElement( 'p', { 
				key: 'description',
				className: 'wc-block-components-payment-method-content__description'
			}, decodeEntities( gatewayData.description ) ),
			gatewayData.logo_url && createElement( 'img', {
				key: 'logo',
				src: gatewayData.logo_url,
				alt: gatewayData.title,
				className: 'wc-block-components-payment-method-logo',
				style: { maxHeight: '40px', maxWidth: '200px' }
			} )
		] );
	};

	// Payment method label component
	const OCPayLabel = ( props ) => {
		return createElement( 'span', {
			className: 'wc-block-components-payment-method-label wc-block-components-payment-method-label--ocpay'
		}, [
			createElement( 'span', {
				key: 'title',
				className: 'wc-block-components-payment-method-label__text'
			}, decodeEntities( gatewayData.title ) ),
			gatewayData.logo_url && createElement( 'img', {
				key: 'logo',
				src: gatewayData.logo_url,
				alt: gatewayData.title,
				className: 'wc-block-components-payment-method-label__logo',
				style: { maxHeight: '24px', marginLeft: '8px' }
			} )
		] );
	};

	// Register the payment method
	const ocpayPaymentMethod = {
		name: gatewayData.name,
		label: createElement( OCPayLabel ),
		content: createElement( OCPayContent ),
		edit: createElement( OCPayContent ),
		canMakePayment: () => gatewayData.canMakePayment,
		ariaLabel: decodeEntities( gatewayData.title ),
		supports: gatewayData.supports || { features: [ 'products' ] },
	};

	registerPaymentMethod( ocpayPaymentMethod );

	console.log( 'OCPay payment method registered for blocks checkout', ocpayPaymentMethod );

} )();
