<?php

class PanicPress_Controller_Admin_Options extends PanicPress_Util_Controller {

	/**
	 * @var PanicPress_Model_Options Options model for this plugin.
	 */
	private $plugin_options = array();

	/**
	 * Constructor
	 *
	 * @param PanicPress_Model_Options Options model for this plugin.
	 *
	 * @return void
	 */
	public function __construct($options) {

		// Save plugin options for later
		$this->plugin_options = $options;

		// Add admin CSS
		add_action('admin_head', array($this, 'admin_head'));

		// Add option menus to general options
		add_action('admin_menu', array($this, 'admin_menu'));

		// If user has not set up their subscription
		if (false === $this->plugin_options->api_key) {

			add_action('admin_notices', array($this, 'subscription_notice'));
		}
	}

	/**
	 * Hook into admin menu actions.
	 * 
	 * @return void
	 */
	public function admin_menu() {
		if ( ! current_user_can('manage_options')) {
			return;
		}

		register_setting('general', PanicPress_Model_Options::OptionName, array($this, "sanitize_api_key"));
		add_settings_section('pp_general_settings', "PanicPress Settings", array($this, 'setting_section_text'), "general");
		add_settings_field('pp_api_key', "API Key", array($this, "setting_api_key"), "general", "pp_general_settings");
	}

	/**
	 * Hook into admin head filter.
	 * 
	 * @return void
	 */
	public function admin_head() {
		wp_enqueue_style("panicpress-admin", plugins_url('/css/admin.css', PANICPRESS_DIR . '/panic-press.php'));
	}

	/**
	 * Echo the text to display for the PanicPress section of the General settings.
	 *
	 * (Currently, nothing!)
	 * 
	 * @return void
	 */
	public function setting_section_text() {}

	/**
	 * [setting_api_key description]
	 * @return [type] [description]
	 */
	public function setting_api_key() {
		echo "<input id='pp_api_key' name='" . PanicPress_Model_Options::OptionName .  "[api_key]' type='text' value='{$this->plugin_options->api_key}'>";
	}

	public function sanitize_api_key($input) {

		$valid = array('api_key' => $input['api_key']);

		// If the newly-entered API key is not valid on the PanicPress server
		if ( ! PanicPress::has_active_subscription($valid['api_key'])) {

			// Revert to whatever they had before
			add_settings_error(PanicPress_Model_Options::OptionName, "bad_api_key", 'Invalid PanicPress API key.  Ensure you have a paid <a href="http://www.panic-press.com">PanicPress</a> account, then visit the <a href="https://www.panic-press.com/account/installation">Installation Page</a> for instructions.');
			$valid['api_key'] = $this->plugin_options->api_key;
		}

		return $valid;
	}

	/**
	 * Display a notice to set up your subscription.
	 * 
	 * @return void
	 */
	public function subscription_notice() {
		// Only display on main plugin page
		if (isset($_GET['page'])) {
			return;
		}

		$as_parts = parse_url(site_url('/'));

		echo $this->getView('subscription-notice', array('site_url' => $as_parts['host']));
	}
}