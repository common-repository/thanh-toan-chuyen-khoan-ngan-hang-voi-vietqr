<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
// Đảm bảo rằng WooCommerce đã được kích hoạt
if (class_exists('WC_Payment_Gateway')) {
    // Tạo class con kế thừa từ WC_Payment_Gateway
    class SBH_Payment_Gateway extends WC_Payment_Gateway
    {


        public $ttckvsbh_instructions;
        public $ttckvsbh_autocheck;
        public $ttckvsbh_locale;
        public $ttckvsbh_account_details;
        public $ttckvsbh_unique_code;
        public $ttckvsbh_qrcode_status;
        public $ttckvsbh_payment_status;
        public $ttckvsbh_tele_token;
        public $ttckvsbh_tele_id;
        public $ttckvsbh_lark_bot_url;
        public $ttckvsbh_success_url;
        static private $URL_QRCODE = "https://api.finan.cc/api/v1/partner/payment-order/create";
        public function __construct()
        {

            $this->id                 = 'ttckvsbh';
            $this->icon               = apply_filters('woocommerce_icon_ttckvsbh', plugins_url('../assets/img/sbh_logo.png', __FILE__));
            $this->has_fields         = false;
            $this->method_title       = esc_html__('Bank Transfer with VietQR powered by SoBanHang', 'ttckvsbh');
            $this->method_description = esc_html__('We auto-generate VietQR code, supporting over 40 banks in Vietnam such as VCB, TCB, ACB, MB, MSB, HDB, SHB... Auto-update the payment status in your orders. Support for balance fluctuation notifications via Lark/Telegram bot. Fast - Simple - Economical!', 'ttckvsbh');

            $this->init_form_fields();
            $this->init_settings();

            $this->title          = esc_html__('Bank transfer', 'ttckvsbh');
            $this->description    = esc_html__('Support banking services with over 40 banks, including Vietcombank (VCB), Techcombank (TCB), Asia Commercial Bank (ACB), Military Bank (MB), Maritime Bank (MSB), HDBank (HDB), and many others.', 'ttckvsbh');
            $this->ttckvsbh_instructions   = $this->get_option('ttckvsbh_instructions');
            $this->ttckvsbh_account_details = get_option(
                'ttckvsbh_accounts',
                [
                    [
                        'ttckvsbh_account_name'   => $this->get_option('ttckvsbh_account_name'),
                        'ttckvsbh_account_number' => $this->get_option('ttckvsbh_account_number'),
                        'ttckvsbh_bank_name'      => $this->get_option('ttckvsbh_bank_name'),
                    ]
                ]
            );
            $this->ttckvsbh_qrcode_status = !empty($this->get_option('ttckvsbh_qrcode_status')) ? $this->get_option('ttckvsbh_qrcode_status') : array('wc-on-hold');
            $this->ttckvsbh_payment_status = !empty($this->get_option('ttckvsbh_payment_status')) ? $this->get_option('ttckvsbh_payment_status') : 'wc-processing';

            $this->ttckvsbh_tele_token   = $this->get_option('ttckvsbh_tele_token');
            $this->ttckvsbh_tele_id   = $this->get_option('ttckvsbh_tele_id');
            $this->ttckvsbh_lark_bot_url   = $this->get_option('ttckvsbh_lark_bot_url');

            $this->ttckvsbh_success_url   = $this->get_option('ttckvsbh_success_url');

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'save_account_details'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));
            add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
            add_action('admin_notices', array($this, 'check_permalink_structure'));
        }

        public function check_permalink_structure()
        {
            if (isset($_GET['section']) && $_GET['section'] === $this->id) {
                $permalink_structure = get_option('permalink_structure');
                if (!$permalink_structure || $permalink_structure === 'plain') {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Attention: The permalink structure is currently set to "Plain". Consider changing it!', 'ttckvsbh') . '</p></div>';
                }

                if (get_woocommerce_currency() !== "VND") {
                    echo '<div class="notice notice-error"><p>' . esc_html__('Attention: Currency does not supported!', 'ttckvsbh') . '</p></div>';
                }
            }
        }
        /**
         * Initialise Gateway Settings Form Fields.
         */
        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => esc_html__('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => esc_html__('Bank Transfer with VietQR powered by SoBanHang', 'ttckvsbh'),
                    'default' => 'no',
                ),
                'ttckvsbh_instructions'    => array(
                    'title'       => esc_html__('Instructions', 'woocommerce'),
                    'type'        => 'textarea',
                    'description' => esc_html__('Instructions that will be added to the thank you page and emails.', 'woocommerce'),
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'ttckvsbh_account_details' => array(
                    'type' => 'account_details',
                ),
                'ttckvsbh_key' => array(
                    'title' => esc_html__('SBH Key', 'ttckvsbh'),
                    'type' => 'password',
                    'description' => esc_html__('Do you have key?', 'ttckvsbh') . ' <a class="button-primary" href="https://bit.ly/3LnTil8" target="_blank">' . esc_html__('Sign Up', 'ttckvsbh') . '</a>',
                    'default' => '',
                ),
                'ttckvsbh_code' => array(
                    'title' => esc_html__('Partner Code', 'ttckvsbh'),
                    'type' => 'text',
                    'description' => '',
                    'default' => '',
                ),
                'ttckvsbh_merchant_bank_account_id' => array(
                    'title' => esc_html__('Active Website', 'ttckvsbh'),
                    'type' => 'password',
                    'default' => '',
                    'description' => esc_html__('After entering your SBH Key and Partner Code, click here to active your website ', 'ttckvsbh') . ' <a id="registersbh" class="button-primary">' . esc_html__('Active', 'woocommerce') . '</a> <label id="sbh_error_merchant"></label>',
                    'custom_attributes' => array(
                        'readonly' => 'readonly'
                    ),
                ),
                'ttckvsbh_hook_status' => array(
                    'title' => esc_html__('Active Webhook', 'ttckvsbh'),
                    'type' => 'text',
                    'default' => esc_html__('Active not success', 'ttckvsbh'),
                    'description' => esc_html__('After entering your SBH Key and Partner Code, click here to active your webhook ', 'ttckvsbh') . ' <a id="registerhook" class="button-primary">' . esc_html__('Active', 'woocommerce') . '</a> <label id="sbh_errorhook"></label>',
                    'custom_attributes' => array(
                        'readonly' => 'readonly'
                    ),
                ),
                'ttckvsbh_qrcode_status' => array(
                    'title'       => esc_html__('Order statuses where you want to display the QRCode', 'ttckvsbh'),
                    'type'        => 'multiselect',
                    'class'       => 'wc-enhanced-select',
                    'description' => esc_html__('Select the statuses for which you want the QRCode to be displayed on the order details page.', 'ttckvsbh'),
                    'options'     => wc_get_order_statuses(),
                    'desc_tip'    => true,
                    'default'     => array('wc-on-hold'),
                ),
                'ttckvsbh_payment_status' => array(
                    'title'       => esc_html__('Order status upon successful payment', 'ttckvsbh'),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => esc_html__('Select the order status you want to display the successful payment page.', 'ttckvsbh'),
                    'options'     => wc_get_order_statuses(),
                    'desc_tip'    => true,
                    'default'     => 'wc-processing',
                ),
                'ttckvsbh_success_url' => array(
                    'title'       => esc_html__('URL for Successful Payment', 'ttckvsbh'),
                    'type'        => 'text',
                    'description' => esc_html__('Leave empty for the default URL.', 'ttckvsbh'),
                    'desc_tip'    => false,
                    'default'     => '',
                ),
                'ttckvsbh_lark_bot_url' => array(
                    'title'       => esc_html__('URL Lark Bot', 'ttckvsbh'),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => esc_html__('Enter the webhook URL for your Lark Bot to send notifications. This URL is usually provided when you create a bot on Lark.', 'ttckvsbh'),
                ),
                'ttckvsbh_tele_token' => array(
                    'title'       => esc_html__('Telegram Bot Token', 'ttckvsbh'),
                    'type'        => 'password',
                    'desc_tip'    => true,
                    'description' => esc_html__('Enter the token for your Telegram Bot. You can obtain this token by creating a new bot through BotFather on Telegram.', 'ttckvsbh'),
                ),
                'ttckvsbh_tele_id' => array(
                    'title'       => esc_html__('Telegram ID', 'ttckvsbh'),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => esc_html__('Enter the ID of the Telegram chat or group where you want to send notifications. You can obtain this ID through other bots like @userinfobot.', 'ttckvsbh'),
                ),
            );
        }

        /**
         * Generate account details html.
         *
         * @return string
         */
        public function generate_account_details_html()
        {

            ob_start();
?>
            <tr valign="top">
                <th scope="row" class="titledesc"><?php esc_html_e('Account details', 'woocommerce'); ?></th>
                <td class="forminp" id="sbh_accounts">
                    <div class="wc_input_table_wrapper">
                        <p><?php esc_html_e('In the beta version, only one banking account is accepted', 'ttckvsbh'); ?></p>
                        <p><?php esc_html_e('For each order, the bank will automatically create a subsidiary account to manage payments. Upon successful completion of the transaction, the balance from this subsidiary account will be immediately transferred to your main account.', 'ttckvsbh'); ?></p>
                        <table class="widefat wc_input_table sortable" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="sort">&nbsp;</th>
                                    <th><?php esc_html_e('Account name', 'woocommerce'); ?></th>
                                    <th><?php esc_html_e('Account number', 'woocommerce'); ?></th>
                                    <th><?php esc_html_e('Bank name', 'woocommerce'); ?></th>
                                </tr>
                            </thead>
                            <tbody class="accounts">
                                <?php
                                $i = -1;
                                if ($this->ttckvsbh_account_details) {
                                    foreach ($this->ttckvsbh_account_details as $account) {
                                        $i++;

                                        echo '<tr class="account">
										<td class="sort"></td>
										<td><input type="text" value="' . esc_attr(wp_unslash($account['ttckvsbh_account_name'])) . '" name="ttckvsbh_account_name[' . esc_attr($i) . ']" id="ttckvsbh_account_name_' . esc_attr($i) . '" /></td>
										<td><input type="text" value="' . esc_attr($account['ttckvsbh_account_number']) . '" name="ttckvsbh_account_number[' . esc_attr($i) . ']" id="ttckvsbh_account_number_' . esc_attr($i) . '" /></td>
										<td> <select name="ttckvsbh_bank_name[' . esc_attr($i) . ']" id="ttckvsbh_bank_name_' . esc_attr($i) . '">
                                            <option value="970422"' . (esc_attr(wp_unslash($account['ttckvsbh_bank_name'])) == '970422' ? ' selected="selected"' : '') . '>MBBank</option>
                                            <option value="970418"' . (esc_attr(wp_unslash($account['ttckvsbh_bank_name'])) == '970418' ? ' selected="selected"' : '') . '>BIDV</option>
                                            <option value="970443"' . (esc_attr(wp_unslash($account['ttckvsbh_bank_name'])) == '970443' ? ' selected="selected"' : '') . '>HDBank</option>
                                            </select>
                                         </td>
									</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7"><a href="#" class="add button"><?php esc_html_e('+ Add account', 'woocommerce'); ?></a> <a href="#" class="remove_rows button"><?php esc_html_e('Remove selected account(s)', 'woocommerce'); ?></a> </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <script type="text/javascript">
                        jQuery(function() {
                            jQuery('#ttckvsbh_accounts').on('click', 'a.add', function() {

                                var size = jQuery('#ttckvsbh_accounts').find('tbody .account').length;

                                jQuery('<tr class="account">\
									<td class="sort"></td>\
									<td><input type="text" name="ttckvsbh_account_name[' + size + ']" /></td>\
									<td><input type="text" name="ttckvsbh_account_number[' + size + ']" /></td>\
									<td><select name="ttckvsbh_bank_name[' + size + ']">\
                                        <option value="970422">MBBank</option>\
                                        <option value="970418">BIDV</option>\
                                        <option value="970443">HDBank</option>\
                                        </select>\
                                    </td>\
								</tr>').appendTo('#ttckvsbh_accounts table tbody');

                                return false;
                            });
                        });
                    </script>
                </td>
            </tr>
<?php
            return ob_get_clean();
        }
        /**
         * Save account details table.
         */
        public function save_account_details()
        {

            $accounts = [];

            // phpcs:disable WordPress.Security.NonceVerification.Missing
            if (
                isset($_POST['ttckvsbh_account_name'], $_POST['ttckvsbh_account_number'], $_POST['ttckvsbh_bank_name'])
            ) {
                $ttckvsbh_account_names   = wc_clean(wp_unslash($_POST['ttckvsbh_account_name']));
                $ttckvsbh_account_numbers = wc_clean(wp_unslash($_POST['ttckvsbh_account_number']));
                $ttckvsbh_bank_names      = wc_clean(wp_unslash($_POST['ttckvsbh_bank_name']));

                $accounts = array_map(function ($name, $number, $bank) {
                    return [
                        'ttckvsbh_account_name'   => $name,
                        'ttckvsbh_account_number' => $number,
                        'ttckvsbh_bank_name'      => $bank,
                    ];
                }, $ttckvsbh_account_names, $ttckvsbh_account_numbers, $ttckvsbh_bank_names);
            }
            // phpcs:enable
            update_option('ttckvsbh_accounts', $accounts);
        }

        /**
         * Output for the order received page.
         *
         * @param int $order_id Order ID.
         */
        public function thankyou_page($order_id)
        {
            $this->bank_details($order_id, false);
        }

        /**
         * Add content to the WC emails.
         *
         * @param WC_Order $order Order object.
         * @param bool     $sent_to_admin Sent to admin.
         * @param bool     $plain_text Email format: plain text or HTML.
         */
        public function email_instructions($order, $sent_to_admin, $plain_text = false)
        {
            /**
             * Filter the email instructions order status.
             *
             * @since 7.4
             * @param string $terms The order status.
             * @param object $order The order object.
             */
            if (!$sent_to_admin && 'ttckvsbh' === $order->get_payment_method() && $order->has_status(apply_filters('woocommerce_ttckvsbh_email_instructions_order_status', 'on-hold', $order))) {
                $this->bank_details($order->get_id(), true);
            }
        }

        /**
         * Get bank details and place into a list format.
         *
         * @param int $order_id Order ID.
         */
        private function bank_details($order_id = '', $is_sent_email = false)
        {

            if (empty($this->ttckvsbh_account_details)) {
                return;
            }

            // Get order and store in $order.
            $order = wc_get_order($order_id);
            $order_status  = "wc-" . $order->get_status();
            $currency = $order->get_currency();
            if ($currency != "VND") {
                return;
            }
            $is_payment = false;
            if ($order_status == $this->ttckvsbh_payment_status) {
                $is_payment = true;
            }
            $qr_note = $order->get_meta("ttckvsbh_qr_note");
            if ($is_payment) {
                echo '
                <section class="woocommerce-sbh-bank-details"> 
                <p class="wc-sbh-bank-details-heading woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">' . esc_html__('Payment Success', 'ttckvsbh') . '</p> 
                <img src="' . trailingslashit(dirname(plugin_dir_url(__FILE__))) . 'assets/img/success-icon.png" style="width: 100px; margin: 20px" id="image-id" /> 
                <style> .woocommerce-sbh-bank-details { text-align: center; } </style> 
                </section>';
            } else if (in_array($order_status, $this->ttckvsbh_qrcode_status)) {
                if ($this->ttckvsbh_instructions) {
                    echo wp_kses_post(wpautop(wptexturize($this->ttckvsbh_instructions)));
                }
                $output = '<div class="woocommerce-sbh-bank">';
                $output .= '<p>' . esc_html__('Using VietQR with info below', 'ttckvsbh') . '</p>';
                $qr_code = $order->get_meta("ttckvsbh_qr_code");
                $ttckvsbh_bank_virtual_account_number = $order->get_meta("ttckvsbh_bank_virtual_account_number");
                if (empty($qr_code)) {
                    $data = $this->get_qrcode($order);
                    $qr_code = $data->data->qr_code;
                    $qr_note = $data->data->bank_note;
                    $ttckvsbh_bank_virtual_account_number = $data->data->bank_virtual_account_number;
                    $order->update_meta_data('ttckvsbh_qr_code', $qr_code);
                    $order->update_meta_data('ttckvsbh_qr_note', $qr_note);
                    $order->update_meta_data('ttckvsbh_bank_virtual_account_number', $ttckvsbh_bank_virtual_account_number);
                    $order->save();
                }
                $image_url = sprintf('https://fin.finan.vn/qrcode?data=%s', urlencode($qr_code));

                $output .= '
                <img src="' . trailingslashit(dirname(plugin_dir_url(__FILE__))) . 'assets/img/vietqr.png" class="image-loading" />
                <img class="image-loading image-main" src="' . esc_url($image_url) . '&border=1" alt="" />
                <img src="' . trailingslashit(dirname(plugin_dir_url(__FILE__))) . 'assets/img/' . $this->bank_name_converter("970422") . '.png" class="image-loading" />
                <div style="display: grid;"><span class="sbh-timer" style="text-align: center;" id="timer_sbh">03:00</span></div>
                <br>
                <a class="sbh-download wp-element-button" id="btnDownloadQR" href="' . esc_url($image_url) . '&border=0&mode=download" title="' . esc_html__('Download QRCode', 'ttckvsbh') . '" target="_self" download="">
                    <span>' . esc_html__('Download', 'woocommerce') . '</span>
                </a>';

                $show_download  = wp_is_mobile();
                $output .= '<style>
                .image-loading{display: block; margin-left: auto; margin-right: auto;width:25%;padding: 10px;}
                .sbh-download{text-align: center; display:' . ($show_download ? 'block' : 'none') . '}
                td{width:25%;}
                .sbh-timer{font-size:25px;}
                .woocommerce-sbh-bank{text-align: center;}
                .image-main{ border: 1px solid black; padding: 10px;}
                </style>';

                // chèn jQuery check status 
                if (strpos($output, 'function fetchStatus') === false) {
                    $output .= '<br><script>
                    jQuery(document).ready(function(){
                        function startTimer(duration, display, cb) {
                            var timer = duration, minutes, seconds,_t;
                            _t = setInterval(function () {
                                minutes = parseInt(timer / 60, 10);
                                seconds = parseInt(timer % 60, 10);
    
                                minutes = minutes < 10 ? "0" + minutes : minutes;
                                seconds = seconds < 10 ? "0" + seconds : seconds;
    
                                display.textContent = minutes + ":" + seconds;
    
                                if (--timer < 0) {
                                    clearInterval(_t);
                                    if(cb)cb();
                                    timer = duration;
                                    window.location.reload(false);
                                }
                            }, 1000);
                            display.style.display="inline-block";
                        }
                        function fetchStatus()
                        {
                            var qrcode_statuses = ' . html_entity_decode(esc_js(json_encode($this->ttckvsbh_qrcode_status))) . ';
                            if(!qrcode_statuses.includes("' . esc_js($order_status) . '")){
                                return;
                            }
                            startTimer(60*3, document.querySelector("#timer_sbh"), function(){
                                document.querySelector("#timer_sbh").style.display = "none";
                                clearInterval(timer);
                            });
                            let timeTemp = 0;
                            
                            const timer = setInterval(function(){
                                jQuery.ajax({
                                    url: "' . site_url() . '/wp-admin/admin-ajax.php",
                                    type: "post",
                                    cache: false, // Để tránh caching
                                    data: {
                                        action: "fetch_order_status_ttckvsbh",
                                        order_id: ' . esc_js($order_id) . ',
                                    },
                                    error: function(response){
                                        // Xử lý lỗi ở đây nếu muốn, ví dụ: alert("Có lỗi xảy ra!");
                                        // console.log(response);
                                    },
                                    success: function(response){
                                        if(response == "' . esc_js($this->ttckvsbh_payment_status) . '"){
                                            var success_url = "' . esc_url($this->ttckvsbh_success_url) . '";
                                            if (success_url != ""){
                                            window.location.href = "' . esc_url($this->ttckvsbh_success_url) . '";
                                            }else{
                                                window.location.reload(false);
                                            }
                                           
                                        }
                                    }
                                });
                                if(timeTemp == 180000){ 
                                    clearInterval(timer);
                                    return;
                                }
                                timeTemp += 3000;
                            }, 3000);
                        }
                        fetchStatus();
                    });
					</script>';
                }

                $output .= '</div>';
                if ($is_sent_email) {
                    $output .= '
                <style>
                    .table-bordered {
                        border: 1px solid rgba(0,0,0,.1);
                    }
                    .sbh-timer{
                        display: none;
                    }
                </style>
                ';
                }
                $output = '<section class="woocommerce-sbh-bank-details"><p class="wc-sbh-bank-details-heading woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">' . esc_html__('Banking transfer', 'ttckvsbh') . '</p>' . $output . '</section>';
                echo $output;
                $sbh_accounts = apply_filters('ttckvsbh_woocommerce_sbh_accounts', $this->ttckvsbh_account_details, $order_id);
                if (!empty($sbh_accounts)) {
                    $account_html = '';
                    $has_details  = false;

                    foreach ($sbh_accounts as $sbh_account) {
                        $sbh_account = (object) $sbh_account;

                        $account_html .= '<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">';
                        // SBH account fields shown on the thanks page and in emails.
                        $account_fields = apply_filters(
                            'ttckvsbh_woocommerce_sbh_account_fields',
                            array(
                                'ttckvsbh_account_name'      => array(
                                    'label' => esc_html__('Account name', 'woocommerce'),
                                    'value' => $sbh_account->ttckvsbh_account_name,
                                ),
                                'ttckvsbh_account_number' => array(
                                    'label' => esc_html__('Account number', 'woocommerce'),
                                    'value' => $ttckvsbh_bank_virtual_account_number,
                                ),
                                'ttckvsbh_bank_name'      => array(
                                    'label' => esc_html__('Bank', 'woocommerce'),
                                    'value' => $this->bank_name_converter($sbh_account->ttckvsbh_bank_name),
                                ),
                                'ttckvsbh_amount'      => array(
                                    'label' => esc_html__('Amount', 'ttckvsbh'),
                                    'value' => wc_price($order->get_total()),
                                ),
                                'ttckvsbh_memo'      => array(
                                    'label' => esc_html__('Memo', 'ttckvsbh'),
                                    'value' => $qr_note,
                                ),
                            ),
                            $order_id
                        );

                        foreach ($account_fields as $field_key => $field) {
                            if (!empty($field['value'])) {
                                $account_html .= '<li class="woocommerce-order-overview__order' . wp_kses_post($field['label']) . '">' . wp_kses_post($field['label']) . '  <strong>' . wp_kses_post(wptexturize($field['value'])) . '</strong></li>' . PHP_EOL;
                                $has_details   = true;
                            }
                        }

                        $account_html .= '</ul>';
                    }

                    if ($has_details) {
                        echo '<section class="woocommerce-sbh-bank-details"><p class="wc-sbh-bank-details-heading">' . esc_html__('Our bank details', 'woocommerce') . '</p>' . wp_kses_post(PHP_EOL . $account_html) . '</section>';
                    }
                }
            }
        }

        public function get_qrcode($order)
        {
            $rd_order = get_option('ttckvsbh_rd_order');
            if (!$rd_order) {
                $rd_order = wp_generate_password(4, false);
                update_option('ttckvsbh_rd_order', $rd_order);
            }

            $url = self::$URL_QRCODE;
            $body = array(
                "partner_code" => $this->get_option('ttckvsbh_code'),
                "merchant_bank_account_id" => $this->get_option('ttckvsbh_merchant_bank_account_id'),
                "partner_order_id" => $rd_order . '' . $order->get_id(),
                "amount" => (int)preg_replace("/([^0-9\\.])/i", "", $order->get_total()),
                "custom_bank_note" => '' . $order->get_id(),
            );

            $args = array(
                "body"        => json_encode($body),
                "headers" => array(
                    "x-api-key" => "sbh-wp-plugin",
                    "x-client-id" => get_site_url(),
                    "content-type" => "application/json",
                    "referer" => home_url($order->get_order_key()),
                    "Authorization" => "Bearer " . $this->get_option('ttckvsbh_key'),
                )
            );

            // echo json_encode($args);
            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                return null;
            }
            // echo json_encode($response);
            $response_code = $response['response']['code'];
            if ($response_code === 200 || $response_code === 201) {
                $body     = wp_remote_retrieve_body($response);
                return json_decode($body);
            }
            return null;
        }

        /**
         * Process the payment and return the result.
         *
         * @param int $order_id Order ID.
         * @return array
         */
        public function process_payment($order_id)
        {

            $order = wc_get_order($order_id);

            if ($order->get_total() > 0) {
                // Mark as on-hold (we're awaiting the payment).
                $order->update_status(apply_filters('woocommerce_ttckvsbh_process_payment_order_status', 'on-hold', $order), esc_html__('Awaiting payment', 'woocommerce'));
            } else {
                $order->payment_complete();
            }

            // Remove cart.
            WC()->cart->empty_cart();

            // Return thankyou redirect.
            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            );
        }

        /**
         * Get country locale if localized.
         *
         * @return array
         */
        public function get_country_locale()
        {

            if (empty($this->ttckvsbh_locale)) {

                // Locale information to be used - only those that are not 'Sort Code'.
                $this->ttckvsbh_locale = apply_filters(
                    'woocommerce_get_sbh_locale',
                    array(
                        'AU' => array(
                            'sortcode' => array(
                                'label' => esc_html__('BSB', 'woocommerce'),
                            ),
                        ),
                        'CA' => array(
                            'sortcode' => array(
                                'label' => esc_html__('Bank transit number', 'woocommerce'),
                            ),
                        ),
                        'IN' => array(
                            'sortcode' => array(
                                'label' => esc_html__('IFSC', 'woocommerce'),
                            ),
                        ),
                        'IT' => array(
                            'sortcode' => array(
                                'label' => esc_html__('Branch sort', 'woocommerce'),
                            ),
                        ),
                        'NZ' => array(
                            'sortcode' => array(
                                'label' => esc_html__('Bank code', 'woocommerce'),
                            ),
                        ),
                        'SE' => array(
                            'sortcode' => array(
                                'label' => esc_html__('Bank code', 'woocommerce'),
                            ),
                        ),
                        'US' => array(
                            'sortcode' => array(
                                'label' => esc_html__('Routing number', 'woocommerce'),
                            ),
                        ),
                        'ZA' => array(
                            'sortcode' => array(
                                'label' => esc_html__('Branch code', 'woocommerce'),
                            ),
                        ),
                    )
                );
            }

            return $this->ttckvsbh_locale;
        }

        function bank_name_converter($bank_code)
        {
            $bank_names = array(
                '970422' => 'MBBank',
                '970418' => 'BIDV',
                '970443' => 'HDBank',
            );
            return isset($bank_names[$bank_code]) ? $bank_names[$bank_code] : $bank_code;
        }
    }
}
