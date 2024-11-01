<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Represents the SBH Blocks Support payment method for WooCommerce.
 *
 * This class extends the AbstractPaymentMethodType class and provides support for the SBH Blocks payment method in WooCommerce.
 * It contains methods and properties specific to the SBH Blocks payment method.
 */
final class WC_SBH_Blocks_Support extends AbstractPaymentMethodType
{
	/**
	 * The name of the payment gateway.
	 *
	 * @var string
	 */
	protected $name = 'ttckvsbh';


	/**
	 * Initializes the class and sets the settings property.
	 *
	 * This method retrieves the WooCommerce TTCKVSBH settings from the database
	 * and assigns them to the $settings property of the class.
	 */
	public function initialize()
	{
		$this->settings = get_option('woocommerce_ttckvsbh_settings', []);
	}

	/**
	 * Checks if the SBH payment gateway is active.
	 *
	 * @return bool Returns true if the SBH payment gateway is active, false otherwise.
	 */
	public function is_active()
	{
		$payment_gateways_class = WC()->payment_gateways();
		$payment_gateways = $payment_gateways_class->payment_gateways();

		return $payment_gateways['ttckvsbh']->is_available();
	}

	/**
	 * Retrieves the script handles for the payment method.
	 *
	 * This function registers and sets translations for the payment method script.
	 *
	 * @return array The script handles for the payment method.
	 */
	public function get_payment_method_script_handles()
	{
		// Path to the asset file
		$asset_path = WC_GATEWAY_SBH_PATH . '/assets/js/index.asset.php';

		// Initialize dependencies array
		$dependencies = [];

		// Check if the asset file exists
		if (file_exists($asset_path)) {
			// Load the asset file
			$asset = require $asset_path;

			// Check if the asset is an array and has dependencies
			if (is_array($asset) && isset($asset['dependencies'])) {
				$dependencies = $asset['dependencies'];
			}
		}

		// Register the payment method script
		wp_register_script(
			'wc-ttckvsbh-blocks-integration',
			WC_GATEWAY_SBH_URL . '/assets/js/index.js',
			$dependencies,
			true
		);

		// Set translations for the payment method script
		wp_set_script_translations(
			'wc-ttckvsbh-blocks-integration',
			'woocommerce-gateway-ttckvsbh'
		);

		// Return the script handles for the payment method
		return ['wc-ttckvsbh-blocks-integration'];
	}

	/**
	 * Retrieves the payment method data for the Bank Transfer gateway.
	 *
	 * @return array The payment method data.
	 */
	public function get_payment_method_data()
	{
		return [
			'title'       => esc_html__('Bank transfer', 'ttckvsbh'),
			'description' => esc_html__('Support banking services with over 40 banks, including Vietcombank (VCB), Techcombank (TCB), Asia Commercial Bank (ACB), Military Bank (MB), Maritime Bank (MSB), HDBank (HDB), and many others.', 'ttckvsbh'),
			'supports'    => $this->get_supported_features(),
			'logo_url'    => WC_GATEWAY_SBH_URL . '/assets/img/vietqr.png',
		];
	}

	/**
	 * Retrieves the supported features of the SBH payment gateway.
	 *
	 * @return array The supported features of the SBH payment gateway.
	 */
	public function get_supported_features()
	{
		$payment_gateways = WC()->payment_gateways->payment_gateways();
		return $payment_gateways['ttckvsbh']->supports;
	}
}
