<?php
/**
 * OCPay Debug Functions
 * 
 * Add ?debug_ocpay_blocks=1 to any page to see detailed information
 */

defined( 'ABSPATH' ) || exit;

// Add debug output for blocks
add_action('wp_footer', function() {
    if (isset($_GET['debug_ocpay_blocks']) && current_user_can('manage_woocommerce')) {
        ?>
        <script>
        console.log('=== OCPay Blocks Debug Information ===');
        
        // Check if WooCommerce Blocks are loaded
        if (typeof window.wc !== 'undefined') {
            console.log('✓ WooCommerce Blocks API available');
            
            if (typeof window.wc.wcBlocksRegistry !== 'undefined') {
                console.log('✓ wcBlocksRegistry available');
            } else {
                console.log('✗ wcBlocksRegistry NOT available');
            }
            
            if (typeof window.wc.wcSettings !== 'undefined') {
                console.log('✓ wcSettings available');
                
                // Check if our data is available
                try {
                    const ocpayData = window.wc.wcSettings.getSetting('ocpay_data', null);
                    if (ocpayData) {
                        console.log('✓ OCPay data found:', ocpayData);
                    } else {
                        console.log('✗ OCPay data NOT found in wcSettings');
                    }
                } catch(e) {
                    console.log('✗ Error accessing OCPay data:', e);
                }
            } else {
                console.log('✗ wcSettings NOT available');
            }
        } else {
            console.log('✗ WooCommerce Blocks API NOT available');
        }
        
        // Check legacy data
        if (typeof window.ocpayBlocksData !== 'undefined') {
            console.log('✓ Legacy ocpayBlocksData available:', window.ocpayBlocksData);
        } else {
            console.log('✗ Legacy ocpayBlocksData NOT available');
        }
        
        // Check if we're on a block-based page
        if (document.querySelector('.wp-block-woocommerce-checkout') || 
            document.querySelector('.wp-block-woocommerce-cart')) {
            console.log('✓ WooCommerce block detected on page');
        } else {
            console.log('? No WooCommerce block detected on page');
        }
        
        console.log('=== End OCPay Blocks Debug ===');
        </script>
        <?php
    }
});

// Add admin debug information
add_action('admin_notices', function() {
    if (isset($_GET['debug_ocpay_admin']) && current_user_can('manage_woocommerce')) {
        $debug_info = array();
        
        // Check WooCommerce status
        $debug_info['woocommerce_active'] = class_exists('WooCommerce');
        $debug_info['woocommerce_version'] = defined('WC_VERSION') ? WC_VERSION : 'Unknown';
        
        // Check blocks status
        $debug_info['blocks_loaded'] = did_action('woocommerce_blocks_loaded') > 0;
        $debug_info['blocks_available'] = class_exists('Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry');
        
        // Check payment gateways
        if (class_exists('WooCommerce')) {
            $gateways = WC()->payment_gateways->payment_gateways();
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            
            $debug_info['all_gateways'] = array_keys($gateways);
            $debug_info['available_gateways'] = array_keys($available_gateways);
            $debug_info['ocpay_registered'] = isset($gateways['ocpay']);
            $debug_info['ocpay_available'] = isset($available_gateways['ocpay']);
            
            if (isset($gateways['ocpay'])) {
                $gateway = $gateways['ocpay'];
                $debug_info['ocpay_enabled'] = $gateway->enabled;
                $debug_info['ocpay_settings'] = $gateway->settings;
            }
        }
        
        echo '<div class="notice notice-info"><pre>';
        echo 'OCPay Debug Information:' . "\n";
        echo wp_json_encode($debug_info, JSON_PRETTY_PRINT);
        echo '</pre></div>';
    }
});