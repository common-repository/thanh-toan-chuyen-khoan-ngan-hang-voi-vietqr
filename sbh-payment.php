<?php

/**
 * Plugin Name: Thanh toán chuyển khoản ngân hàng với VietQRPro từ Sổ Bán Hàng
 * Plugin URI: https://sobanhang.com/plugin-wordpress/
 * Description: Chuyển Khoản Ngân Hàng, tự động xác nhận thanh toán với VietQR. Thông báo biến động số dư qua Lark, Telegram.
 * Author: SBH Team
 * Author URI: https://sobanhang.com
 * Text Domain: ttckvsbh
 * Domain Path: /languages
 * Version: 1.0.7
 * Tested up to: 6.5
 * WC requires at least: 6.0
 * WC tested up to: 8.0
 * License: GNU General Public License v3.0
 */

defined('ABSPATH') or exit;

/**
 * Calculate the base path of the plugin.
 *
 * This function uses the `plugin_dir_path` function to determine the base path of the current plugin file.
 *
 * @return string The base path of the plugin.
 */
$plugin_base_path = plugin_dir_path(__FILE__);

/**
 * Get the basename of the plugin's base path.
 *
 * This function uses the `basename` function to retrieve the basename of the plugin's base path.
 *
 * @return string The basename of the plugin's base path.
 */
$plugin_basename = basename($plugin_base_path);

/**
 * Determine the URL of the plugin.
 *
 * This function uses the `plugins_url` function to determine the URL of the plugin, without the trailing slash.
 *
 * @return string The URL of the plugin.
 */
$plugin_url = untrailingslashit(plugins_url($plugin_basename, basename(__FILE__)));

/**
 * Define constants for the SBH payment gateway.
 */
define('WC_GATEWAY_SBH_URL', $plugin_url);
define('WC_GATEWAY_SBH_PATH', untrailingslashit($plugin_base_path));

/**
 * Declare compatibility with certain features of WooCommerce.
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );


/**
 * Adds action to support WooCommerce blocks.
 */
add_action( 'woocommerce_blocks_loaded', 'woocommerce_sbh_woocommerce_blocks_support' );

/**
 * Callback function to add support for WooCommerce blocks.
 */
function woocommerce_sbh_woocommerce_blocks_support() {
    if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        require_once dirname( __FILE__ ) . '/classes/class-wc-gateway-sbh-blocks-support.php';
        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
                $payment_method_registry->register( new WC_SBH_Blocks_Support );
            }
        );
    }
}

// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

//load plugin code
add_action('plugins_loaded', 'ttckvsbh_gateway_init');
//register SoBanHang gateway
add_filter('woocommerce_payment_gateways', 'ttckvsbh_add_gateways');
//setting for plugin
add_action('init', 'ttckvsbh_add_setting');

// You can also register a webhook here
add_action('woocommerce_api_ttckvsbh', 'ttckvsbh_webhook');
add_action('plugins_loaded', 'ttckvsbh_load_textdomain');

// Đăng ký script JavaScript và liên kết nó với trang cài đặt của cổng thanh toán
add_action('admin_enqueue_scripts', 'enqueue_ttckvsbh_admin_scripts');
add_action('wp_enqueue_scripts', 'enqueue_ttckvsbh_scripts');

add_action('wp_ajax_nopriv_fetch_order_status_ttckvsbh',  'fetch_order_status_ttckvsbh');
add_action('wp_ajax_fetch_order_status_ttckvsbh',  'fetch_order_status_ttckvsbh');

/**
 * Enqueues the necessary scripts and styles for the admin area of the "thanh-toan-chuyen-khoan-ngan-hang-voi-vietqr" plugin.
 *
 * This function is responsible for loading the required JavaScript and CSS files for the admin area of the plugin.
 * It also generates and saves a unique client ID and API key if they are not already set.
 *
 * @since 1.0.0
 */
function enqueue_ttckvsbh_admin_scripts()
{
    // Enqueue the JavaScript file for the admin area
    wp_enqueue_script('ttckvsbh-admin-payment', plugins_url('assets/js/sbh_admin.js', __FILE__), array('jquery'), null, true);

    // Enqueue the CSS file for the admin area
    wp_enqueue_style('ttckvsbh-admin-payment', plugins_url('assets/css/admin-style.css', __FILE__));

    // Generate and save a unique client ID if it is not already set
    $client_id = get_option('ttckvsbh_client_id');
    if (!$client_id) {
        $unique_code = wp_generate_uuid4();
        update_option('ttckvsbh_client_id', $unique_code);
    }

    // Generate and save a unique API key if it is not already set
    $api_key = get_option('ttckvsbh_api_key');
    if (!$api_key) {
        $unique_code = wp_generate_password(12, false);
        update_option('ttckvsbh_api_key', $unique_code);
    }

    // Prepare the site data to be passed to the JavaScript file
    $site_data = array(
        'client_id' => $client_id,
        'api_key' => $api_key
    );

    // Localize the JavaScript file with the site data
    wp_localize_script('ttckvsbh-admin-payment', 'siteData', $site_data);
}

function enqueue_ttckvsbh_scripts()
{
    // error_log("enqueue_sbh_scripts function called.");
    wp_enqueue_script('ttckvsbh-payment', plugins_url('assets/js/sbh.js', __FILE__), array('jquery'), null, true);
}

function ttckvsbh_webhook()
{
    if (isset($_SERVER['HTTP_X_CLIENT_ID']) && isset($_SERVER['HTTP_X_API_KEY'])) {
        $client_id = sanitize_text_field($_SERVER['HTTP_X_CLIENT_ID']);
        $api_key = sanitize_text_field($_SERVER['HTTP_X_API_KEY']);

        $in_api_key = get_option('ttckvsbh_api_key');
        $in_client_id = get_option('ttckvsbh_client_id');
        if ($in_api_key != $api_key || $in_client_id != $client_id) {
            wp_send_json_error('Mismatch api key', 400);
            return;
        }
        $input = file_get_contents("php://input");
        $decoded_input = json_decode($input, true);
        if (is_array($decoded_input)) {
            $data = array_map('sanitize_text_field', $decoded_input);

            if (!isset($data['partner_order_id']) || !isset($data['amount'])) {
                wp_send_json_error('Missing parameters', 400);
                return;
            }

            $order_id = substr($data['partner_order_id'], 4);

            $order = wc_get_order($order_id);
            if (!$order || !($order instanceof WC_Order)) {
                wp_send_json_error('Order not found', 404);
                return;
            }

            if ($data['amount'] >= $order->get_total()) {
                // Tính số tiền thừa
                $excess_amount = $data['amount'] - $order->get_total();

                $gateway = new SBH_Payment_Gateway();
                $payment_status_option = $gateway->ttckvsbh_payment_status;

                // Tách chuỗi thành mảng
                $status_parts = explode('-', $payment_status_option);

                if (count($status_parts) === 2 && $status_parts[0] === 'wc') {
                    $payment_status = $status_parts[1];

                    if ($payment_status === 'completed' || $payment_status === 'processing') {
                        // Cập nhật trạng thái đơn hàng theo trạng thái trong tùy chọn
                        $order->update_status($payment_status);

                        // Thêm ghi chú về số tiền thừa
                        $order->add_order_note(esc_html__('Excess amount: ', 'ttckvsbh') . wc_price($excess_amount));

                        // Create content to send bot
                        $order_id = $order->get_id();
                        $order_total = $order->get_total();
                        $txn_time = esc_html($data['txn_time']);
                        $order_url = admin_url('post.php?post=' . $order_id . '&action=edit');
                        $bank_account_number = esc_html($data['bank_account_number']);

                        $content = sprintf(
                            esc_html__('Successful payment for order %s, amount %s, transaction time %s for account %s, detail at %s.', 'ttckvsbh'),
                            $order_id,
                            $order_total,
                            $txn_time,
                            $bank_account_number,
                            $order_url
                        );
                        ttckvsbh_send_noti_to_bot($gateway, $order, $content);
                    } else {
                        // Nếu trạng thái thanh toán không hợp lệ, ghi chú và cập nhật trạng thái là failed
                        $order->add_order_note(esc_html__('Invalid payment status in options.', 'ttckvsbh'));
                        $order->update_status('failed');
                    }
                } else {
                    // Trường hợp chuỗi không hợp lệ
                    $order->add_order_note(esc_html__('Invalid payment status format in options.', 'ttckvsbh'));
                    $order->update_status('failed');
                }
            } else {
                $order->add_order_note(esc_html__('Payment status goes failed.', 'ttckvsbh'));
                $order->update_status('failed');
            }
            wp_send_json_success('Updated successfully', 200);
        } else {
            wp_send_json_error('Wrong data response', 400);
        }
    } else {
        wp_send_json_error('Missing required headers', 400);
        return;
    }
}

function ttckvsbh_gateway_init()
{
    require_once(plugin_dir_path(__FILE__) . 'classes/class-sbh-wc-gateway.php');
}

function ttckvsbh_load_textdomain()
{
    load_plugin_textdomain('ttckvsbh', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

function ttckvsbh_add_gateways($gateways)
{
    $gateways[] = 'SBH_Payment_Gateway';
    return $gateways;
}

function ttckvsbh_add_setting()
{
    // Nếu WooCommerce plugin đang hoạt động
    if (is_plugin_active('woocommerce/woocommerce.php')) {
        // Thêm "Settings" link
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ttckvsbh_add_settings_link');
    }
}
function ttckvsbh_add_settings_link($links)
{
    $settings = array('<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=ttckvsbh') . '">' . esc_html__('Settings', 'woocommerce') . '</a>');
    $links    = array_reverse(array_merge($links, $settings));

    return $links;
}

function fetch_order_status_ttckvsbh()
{
    // Kiểm tra và làm sạch 'order_id'
    $order_id = isset($_REQUEST['order_id']) ? sanitize_text_field($_REQUEST['order_id']) : null;

    // Kiểm tra xem 'order_id' có phải là số và không rỗng
    if (empty($order_id) || !is_numeric($order_id)) {
        echo 'pending';
        wp_die();
    }

    // Lấy đối tượng đơn hàng
    $order = wc_get_order($order_id);

    // Kiểm tra xem đối tượng đơn hàng có hợp lệ không
    if (!$order) {
        echo 'invalid order';
        wp_die();
    }

    // Lấy dữ liệu từ đơn hàng
    $order_data = $order->get_data();

    // Kiểm tra và làm sạch trạng thái đơn hàng
    $status = isset($order_data['status']) ? esc_attr($order_data['status']) : 'unknown';

    // In ra trạng thái đơn hàng đã được làm sạch và thoát
    echo 'wc-' . esc_html(trim($status));
    wp_die();
}

function ttckvsbh_send_noti_to_bot($gateway, $order, $content)
{
    // function body goes here
    $lark_bot = $gateway->ttckvsbh_lark_bot_url;
    if (!empty($lark_bot)) {
        $url = $lark_bot;
        $body = [
            'msg_type' => 'text',
            'content' => ['text' => $content]
        ];
        $args = [
            "body" => json_encode($body),
            "headers" => ["content-type" => "application/json"]
        ];
        // echo json_encode($args);
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $order->add_order_note(printf(esc_html__('Send to Lark failed: %s', 'ttckvsbh'), esc_html($error_message)));
        } else {
            $order->add_order_note(esc_html__('Send to Lark success', 'ttckvsbh'));
        }
    }

    $tele_id = $gateway->ttckvsbh_tele_id;
    $tele_token = $gateway->ttckvsbh_tele_token;
    if (!empty($tele_id) && !empty($tele_token)) {
        $url = "https://api.telegram.org/bot{$tele_token}/sendMessage";
        $body = [
            'chat_id' => $tele_id,
            'text' => $content
        ];
        $args = [
            "body" => json_encode($body),
            "headers" => ["content-type" => "application/json"]
        ];
        $response = wp_remote_post($url, $args);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $order->add_order_note(printf(esc_html__('Send to Telegram failed: %s', 'ttckvsbh'), esc_html($error_message)));
        } else {
            $order->add_order_note(esc_html__('Send to Telegram success', 'ttckvsbh'));
        }
    }
}
