<?php
/**
 * OCPay Order Status Diagnostic Tool
 * 
 * This tool helps diagnose why order #99 (or any order) is not updating
 * Place this file in the plugin directory and access via:
 * http://yoursite.local/wp-content/plugins/ocpay-woocommerce/check-order-status.php
 * 
 * @package OCPay_WooCommerce
 */

// Security check - only run if accessed directly
if (!defined('ABSPATH')) {
    // Load WordPress
    require_once('../../../../../wp-load.php');
}

// Check if user is admin
if (!current_user_can('manage_woocommerce')) {
    wp_die('Unauthorized - You must be an administrator to access this tool.');
}

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 99;
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>OCPay Order Status Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #0073aa; margin-top: 30px; }
        .success { color: #46b450; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        .warning { color: #ffb900; font-weight: bold; }
        .info { color: #00a0d2; font-weight: bold; }
        pre { background: #f9f9f9; border: 1px solid #ddd; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .button { display: inline-block; padding: 10px 20px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px; margin: 5px; border: none; cursor: pointer; }
        .button:hover { background: #005177; }
        .button-secondary { background: #f0f0f1; color: #2c3338; }
        .button-secondary:hover { background: #dcdcde; }
        .status-box { padding: 15px; border-radius: 4px; margin: 15px 0; }
        .status-box.success { background: #d4edda; border: 1px solid #c3e6cb; }
        .status-box.error { background: #f8d7da; border: 1px solid #f5c6cb; }
        .status-box.warning { background: #fff3cd; border: 1px solid #ffeaa7; }
        .status-box.info { background: #d1ecf1; border: 1px solid #bee5eb; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f1; font-weight: bold; }
        .log-output { max-height: 400px; overflow-y: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 OCPay Order Status Diagnostic Tool</h1>
        
        <form method="GET" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px;">
            <label for="order_id">Order ID to Check:</label>
            <input type="number" name="order_id" id="order_id" value="<?php echo esc_attr($order_id); ?>" style="padding: 5px; margin: 0 10px;">
            <button type="submit" class="button">Check Order</button>
            <button type="submit" name="action" value="force_check" class="button">Force Status Check Now</button>
            <button type="submit" name="action" value="test_api" class="button button-secondary">Test API Connection</button>
        </form>

        <?php
        // ========================================
        // 1. ORDER INFORMATION
        // ========================================
        echo '<h2>📦 Order Information (Order #' . esc_html($order_id) . ')</h2>';
        
        $order = wc_get_order($order_id);
        if (!$order) {
            echo '<div class="status-box error">';
            echo '<p class="error">✗ Order #' . esc_html($order_id) . ' not found!</p>';
            echo '<p>Please check the order ID and try again.</p>';
            echo '</div>';
        } else {
            echo '<div class="status-box info">';
            echo '<table>';
            echo '<tr><th>Property</th><th>Value</th></tr>';
            echo '<tr><td>Order Number</td><td>#' . esc_html($order->get_order_number()) . '</td></tr>';
            echo '<tr><td>Order ID</td><td>' . esc_html($order->get_id()) . '</td></tr>';
            echo '<tr><td>Order Status</td><td><strong>' . esc_html($order->get_status()) . '</strong></td></tr>';
            echo '<tr><td>Payment Method</td><td>' . esc_html($order->get_payment_method()) . '</td></tr>';
            echo '<tr><td>Total</td><td>' . esc_html($order->get_total()) . ' ' . esc_html($order->get_currency()) . '</td></tr>';
            echo '<tr><td>Date Created</td><td>' . esc_html($order->get_date_created()->date('Y-m-d H:i:s')) . '</td></tr>';
            
            // Order age
            $age_seconds = time() - $order->get_date_created()->getTimestamp();
            $age_hours = round($age_seconds / 3600, 1);
            echo '<tr><td>Order Age</td><td>' . esc_html($age_hours) . ' hours';
            if ($age_hours > 24) {
                echo ' <span class="warning">(⚠ Older than 24 hours - automatic polling may have stopped)</span>';
            }
            echo '</td></tr>';
            echo '</table>';
            echo '</div>';

            // ========================================
            // 2. PAYMENT REFERENCE CHECK
            // ========================================
            echo '<h2>🔑 Payment Reference Check</h2>';
            
            $payment_ref = $order->get_meta('_ocpay_payment_ref');
            $payment_url = $order->get_meta('_ocpay_payment_url');
            
            if (empty($payment_ref)) {
                echo '<div class="status-box error">';
                echo '<p class="error">✗ NO PAYMENT REFERENCE FOUND!</p>';
                echo '<p><strong>This is likely the problem!</strong></p>';
                echo '<p>The order was created but no payment reference was stored. This means:</p>';
                echo '<ul>';
                echo '<li>The payment link generation may have failed</li>';
                echo '<li>The API request to OCPay did not complete successfully</li>';
                echo '<li>Status polling cannot check this order without a payment reference</li>';
                echo '</ul>';
                echo '<p><strong>Solution:</strong> Check the OCPay logs for errors during order creation.</p>';
                echo '</div>';
            } else {
                echo '<div class="status-box success">';
                echo '<p class="success">✓ Payment reference found!</p>';
                echo '<table>';
                echo '<tr><td><strong>Payment Reference:</strong></td><td><code>' . esc_html($payment_ref) . '</code></td></tr>';
                if (!empty($payment_url)) {
                    echo '<tr><td><strong>Payment URL:</strong></td><td><code>' . esc_html(substr($payment_url, 0, 50)) . '...</code></td></tr>';
                }
                echo '</table>';
                echo '</div>';
            }

            // ========================================
            // 3. API CLIENT & GATEWAY SETTINGS
            // ========================================
            echo '<h2>⚙️ OCPay Gateway Settings</h2>';
            
            $gateways = WC()->payment_gateways->payment_gateways();
            $gateway = isset($gateways['ocpay']) ? $gateways['ocpay'] : null;
            
            if (!$gateway) {
                echo '<div class="status-box error">';
                echo '<p class="error">✗ OCPay gateway not found!</p>';
                echo '</div>';
            } else {
                $api_mode = $gateway->get_option('api_mode', 'sandbox');
                $api_key_sandbox = $gateway->get_option('api_key_sandbox');
                $api_key_live = $gateway->get_option('api_key_live');
                $current_api_key = ($api_mode === 'live') ? $api_key_live : $api_key_sandbox;
                $order_status_setting = $gateway->get_option('order_status_after_payment', 'processing');
                
                echo '<div class="status-box info">';
                echo '<table>';
                echo '<tr><td><strong>API Mode:</strong></td><td>' . esc_html(strtoupper($api_mode)) . '</td></tr>';
                echo '<tr><td><strong>API Key (Sandbox):</strong></td><td>' . (!empty($api_key_sandbox) ? '<span class="success">✓ SET</span>' : '<span class="error">✗ NOT SET</span>') . '</td></tr>';
                echo '<tr><td><strong>API Key (Live):</strong></td><td>' . (!empty($api_key_live) ? '<span class="success">✓ SET</span>' : '<span class="error">✗ NOT SET</span>') . '</td></tr>';
                echo '<tr><td><strong>Current API Key:</strong></td><td>' . (!empty($current_api_key) ? '<span class="success">✓ SET (First 10 chars: ' . substr($current_api_key, 0, 10) . '...)</span>' : '<span class="error">✗ NOT SET</span>') . '</td></tr>';
                echo '<tr><td><strong>Order Status After Payment:</strong></td><td>' . esc_html($order_status_setting) . '</td></tr>';
                echo '</table>';
                echo '</div>';
                
                // API Connection Test
                if ($action === 'test_api') {
                    echo '<h2>🌐 API Connection Test</h2>';
                    
                    if (empty($current_api_key)) {
                        echo '<div class="status-box error">';
                        echo '<p class="error">✗ Cannot test API: API key not configured</p>';
                        echo '</div>';
                    } else {
                        require_once(plugin_dir_path(__FILE__) . 'includes/class-ocpay-api-client.php');
                        require_once(plugin_dir_path(__FILE__) . 'includes/class-ocpay-validator.php');
                        require_once(plugin_dir_path(__FILE__) . 'includes/class-ocpay-logger.php');
                        
                        $api_client = new OCPay_API_Client($current_api_key, $api_mode);
                        $test_result = $api_client->test_connection();
                        
                        if (is_wp_error($test_result)) {
                            echo '<div class="status-box error">';
                            echo '<p class="error">✗ API Connection Failed</p>';
                            echo '<p>Error: ' . esc_html($test_result->get_error_message()) . '</p>';
                            echo '</div>';
                        } else {
                            echo '<div class="status-box success">';
                            echo '<p class="success">✓ API Connection Successful!</p>';
                            echo '<pre>' . print_r($test_result, true) . '</pre>';
                            echo '</div>';
                        }
                    }
                }
            }

            // ========================================
            // 4. FORCE STATUS CHECK
            // ========================================
            if ($action === 'force_check' && !empty($payment_ref)) {
                echo '<h2>🔄 Force Payment Status Check</h2>';
                
                require_once(plugin_dir_path(__FILE__) . 'includes/class-ocpay-api-client.php');
                require_once(plugin_dir_path(__FILE__) . 'includes/class-ocpay-validator.php');
                require_once(plugin_dir_path(__FILE__) . 'includes/class-ocpay-logger.php');
                require_once(plugin_dir_path(__FILE__) . 'includes/class-ocpay-status-checker.php');
                
                echo '<div class="status-box info">';
                echo '<p>Checking payment status with OCPay API...</p>';
                
                $api_client = new OCPay_API_Client($current_api_key, $api_mode);
                $status_response = $api_client->check_payment_status($payment_ref);
                
                if (is_wp_error($status_response)) {
                    echo '<p class="error">✗ API Error: ' . esc_html($status_response->get_error_message()) . '</p>';
                } else {
                    echo '<p class="success">✓ API Response Received</p>';
                    echo '<pre>' . print_r($status_response, true) . '</pre>';
                    
                    $payment_status = isset($status_response['status']) ? strtoupper($status_response['status']) : 'UNKNOWN';
                    
                    echo '<p><strong>Payment Status: </strong><span class="' . ($payment_status === 'CONFIRMED' ? 'success' : ($payment_status === 'FAILED' ? 'error' : 'warning')) . '">' . esc_html($payment_status) . '</span></p>';
                    
                    // Update order status
                    if ($payment_status === 'CONFIRMED') {
                        echo '<p>Updating order to ' . esc_html($order_status_setting) . '...</p>';
                        
                        $order->set_status($order_status_setting);
                        $order->update_meta_data('_ocpay_payment_confirmed_at', current_time('mysql'));
                        $order->add_order_note('OCPay payment confirmed via manual status check. Payment Reference: ' . $payment_ref);
                        $order->save();
                        
                        echo '<p class="success">✓ Order status updated to ' . esc_html($order_status_setting) . '!</p>';
                        
                        // Reload order to show new status
                        $order = wc_get_order($order_id);
                        echo '<p><strong>New Order Status:</strong> ' . esc_html($order->get_status()) . '</p>';
                    } elseif ($payment_status === 'FAILED') {
                        echo '<p class="error">Payment has failed. Updating order to on-hold...</p>';
                        
                        $order->set_status('on-hold');
                        $order->update_meta_data('_ocpay_payment_failed_at', current_time('mysql'));
                        $order->add_order_note('OCPay payment failed. Payment Reference: ' . $payment_ref);
                        $order->save();
                        
                        echo '<p>Order status updated to on-hold.</p>';
                    } else {
                        echo '<p class="warning">Payment is still PENDING. No changes made to order.</p>';
                    }
                }
                echo '</div>';
            }

            // ========================================
            // 5. CRON JOB STATUS
            // ========================================
            echo '<h2>⏰ Cron Job Status</h2>';
            
            $next_event = wp_next_scheduled('wp_scheduled_event_ocpay_check_payment_status');
            
            if (!$next_event) {
                echo '<div class="status-box error">';
                echo '<p class="error">✗ Cron job NOT scheduled!</p>';
                echo '<p><strong>This is a problem!</strong> The automatic status polling will not run.</p>';
                echo '<p><strong>Solution:</strong></p>';
                echo '<ol>';
                echo '<li>Go to Plugins page</li>';
                echo '<li>Deactivate "OCPay for WooCommerce"</li>';
                echo '<li>Reactivate "OCPay for WooCommerce"</li>';
                echo '</ol>';
                echo '</div>';
            } else {
                $time_until = $next_event - time();
                $minutes_until = round($time_until / 60);
                
                echo '<div class="status-box success">';
                echo '<p class="success">✓ Cron job is scheduled!</p>';
                echo '<table>';
                echo '<tr><td><strong>Next Scheduled Run:</strong></td><td>' . wp_date('Y-m-d H:i:s', $next_event) . '</td></tr>';
                echo '<tr><td><strong>Time Until Next Run:</strong></td><td>' . abs($minutes_until) . ' minutes</td></tr>';
                echo '<tr><td><strong>Interval:</strong></td><td>Every 20 minutes</td></tr>';
                echo '</table>';
                echo '</div>';
            }

            // ========================================
            // 6. ORDER NOTES & HISTORY
            // ========================================
            echo '<h2>📝 Order Notes & History</h2>';
            
            $notes = wc_get_order_notes(['order_id' => $order_id, 'limit' => 10]);
            
            if (empty($notes)) {
                echo '<div class="status-box warning">';
                echo '<p class="warning">No order notes found</p>';
                echo '</div>';
            } else {
                echo '<div class="status-box info">';
                echo '<table>';
                echo '<tr><th>Date</th><th>Note</th></tr>';
                foreach ($notes as $note) {
                    echo '<tr>';
                    echo '<td>' . esc_html($note->date_created->date('Y-m-d H:i:s')) . '</td>';
                    echo '<td>' . wp_kses_post($note->content) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }

            // ========================================
            // 7. RECENT LOGS
            // ========================================
            echo '<h2>📋 Recent OCPay Logs</h2>';
            
            if (class_exists('OCPay_Logger')) {
                $logger = OCPay_Logger::get_instance();
                $logs = $logger->get_logs();
                
                if (empty($logs)) {
                    echo '<div class="status-box warning">';
                    echo '<p class="warning">No logs found</p>';
                    echo '</div>';
                } else {
                    echo '<div class="status-box info log-output">';
                    echo '<pre>' . esc_html($logs) . '</pre>';
                    echo '</div>';
                }
            } else {
                echo '<div class="status-box error">';
                echo '<p class="error">Logger class not available</p>';
                echo '</div>';
            }
        }

        // ========================================
        // 8. SUMMARY & RECOMMENDATIONS
        // ========================================
        if ($order) {
            echo '<h2>💡 Summary & Recommendations</h2>';
            echo '<div class="status-box info">';
            
            $issues = [];
            $solutions = [];
            
            // Check for issues
            if (empty($payment_ref)) {
                $issues[] = 'No payment reference stored for this order';
                $solutions[] = 'Check OCPay logs to see if payment link generation failed';
                $solutions[] = 'Verify API keys are correct';
            }
            
            if (!$next_event) {
                $issues[] = 'Cron job not scheduled';
                $solutions[] = 'Deactivate and reactivate the OCPay plugin';
            }
            
            if ($age_hours > 24) {
                $issues[] = 'Order is older than 24 hours (automatic polling stops after 24 hours)';
                $solutions[] = 'Use the "Force Status Check Now" button above to manually check payment status';
            }
            
            if (empty($current_api_key)) {
                $issues[] = 'API key not configured for current mode (' . $api_mode . ')';
                $solutions[] = 'Go to WooCommerce → Settings → Payments → OCPay and set the API key';
            }
            
            if ($order->get_status() === 'pending' && !empty($payment_ref)) {
                $issues[] = 'Order is still pending - payment may not have been completed by customer';
                $solutions[] = 'Check if customer completed payment at OCPay';
                $solutions[] = 'Use "Force Status Check Now" button to manually verify payment status';
            }
            
            // Display issues
            if (!empty($issues)) {
                echo '<h3>❌ Issues Found:</h3>';
                echo '<ul>';
                foreach ($issues as $issue) {
                    echo '<li class="error">' . esc_html($issue) . '</li>';
                }
                echo '</ul>';
                
                echo '<h3>✅ Recommended Solutions:</h3>';
                echo '<ol>';
                foreach ($solutions as $solution) {
                    echo '<li>' . esc_html($solution) . '</li>';
                }
                echo '</ol>';
            } else {
                echo '<p class="success">✓ No major issues detected!</p>';
                echo '<p>If the order is still pending, the customer may not have completed payment yet, or the payment is still being processed by the bank.</p>';
            }
            
            echo '</div>';
        }
        ?>

        <hr>
        <p><a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=ocpay'); ?>" class="button">Go to OCPay Settings</a></p>
        <p><a href="<?php echo admin_url('edit.php?post_type=shop_order'); ?>" class="button button-secondary">View All Orders</a></p>
    </div>
</body>
</html>
