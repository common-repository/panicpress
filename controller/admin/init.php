<?php

class PanicPress_Controller_Admin_Init extends PanicPress_Util_Controller {

	/**
	 * @var PanicPress_Model_Options The options for this plugin.
	 */
	private $options;

	/**
	 * Hook into wordpress actions.
	 *
	 * @param PanicPress_Model_Options Options model for this plugin.
	 *
	 * @return void
	 */
	public function __construct($options) {

		$this->options = $options;

		add_action('admin_init', array($this, 'init'));
	}

	/**
	 * Ensure this device has access to the admin section of the blog.
	 * 
	 * @return void
	 */
	public function init() {

		// If subscription isn't set up (or PP site is down), don't try to auth
		if ( ! PanicPress::has_active_subscription($this->options->api_key)) {
			return;
		}

		// If there's no cookie identifying this device, set one
		if ( ! isset($_COOKIE[PanicPress::COOKIE_DEVICE_ID])) {

			// Generate random device ID, save it in cookies for a year
			$_COOKIE[PanicPress::COOKIE_DEVICE_ID] = wp_generate_password(32, false);
			setcookie(PanicPress::COOKIE_DEVICE_ID, $_COOKIE[PanicPress::COOKIE_DEVICE_ID], strtotime("+1 year"), ADMIN_COOKIE_PATH, COOKIE_DOMAIN);
		}

		// If this device has been rejected
		if ($this->options->is_rejected($_COOKIE[PanicPress::COOKIE_DEVICE_ID])) {

			// Display "Waiting" page... FOREVER </pinkipie>
			echo $this->getView("waiting", array("device_name" => $_COOKIE[PanicPress::COOKIE_DEVICE_NAME]));
			exit;
		}

		// If this device isn't authorized
		if ( ! $this->options->is_authorized($_COOKIE[PanicPress::COOKIE_DEVICE_ID])) {

			// If we don't have a name set for this device
			if ( ! isset($_COOKIE[PanicPress::COOKIE_DEVICE_NAME])) {

				// Was one provided by form?
				if (isset($_POST[PanicPress::COOKIE_DEVICE_NAME]) && strlen($_POST[PanicPress::COOKIE_DEVICE_NAME]) > 0) {
					// Set it, continue
					$_COOKIE[PanicPress::COOKIE_DEVICE_NAME] = trim($_POST[PanicPress::COOKIE_DEVICE_NAME]);
					setcookie(PanicPress::COOKIE_DEVICE_NAME, $_COOKIE[PanicPress::COOKIE_DEVICE_NAME], strtotime("+1 year"), ADMIN_COOKIE_PATH, COOKIE_DOMAIN);
				} 
				// If not provided by form, display form, exit
				else {
					echo $this->getView("identify", array("cookie_input_name" => PanicPress::COOKIE_DEVICE_NAME));
					exit;
				}
			}

			// If we haven't yet sent a request for authorization
			$send_request_option_name = "pp_send_" . $_COOKIE[PanicPress::COOKIE_DEVICE_ID];
			if ( IS_DEV || ! get_transient($send_request_option_name)) {

				// Check with main server
				PanicPress::api('validate', array('body' => array('api_key' => $this->options->api_key, 'device_id' => $_COOKIE[PanicPress::COOKIE_DEVICE_ID], 'device_name' => $_COOKIE[PanicPress::COOKIE_DEVICE_NAME], 'ip' =>$_SERVER['REMOTE_ADDR'])));
				set_transient($send_request_option_name, true, PanicPress::AUTH_REQUEST_DELAY);
			}
	
			// Display "Waiting" page until user authorizes request
			echo $this->getView("waiting", array("device_name" => $_COOKIE[PanicPress::COOKIE_DEVICE_NAME]));
			exit;
		}
	}
}