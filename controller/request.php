<?php

/**
 * Responds to requests from the PanicPress server.
 */
class PanicPress_Controller_Request extends PanicPress_Util_Controller {

	/**
	 * @var PanicPress_Model_Options The options for this plugin.
	 */
	private $options;

	/**
	 * Constructor, responds to server requests.
	 * 
	 * @param PanicPress_Model_Options $options The options for this plugin.
	 */
	public function __construct($options) {

		$this->options = $options;

		// Is this a valid shutdown request?
		if (isset($_REQUEST[PanicPress::ACTION_PANIC]) && $this->is_valid_request()) {

			// Disable admin section
			$this->options->set(array('shutdown' => true))->save();

			// Let main server know that request was received and acted upon
			echo PanicPress::RESPONSE_SUCCESS . PanicPress::ACTION_PANIC;
			exit;
		}

		// Is this a valid restore request?
		else if (isset($_REQUEST[PanicPress::ACTION_SAFE]) && $this->is_valid_request()) {

			// Re-enable admin sction
			$this->options->set(array('shutdown' => false))->save();

			// Let main server know that request was received and acted upon
			echo PanicPress::RESPONSE_SUCCESS . PanicPress::ACTION_SAFE;
			exit;
		}

		// Is this a valid request to approve authorization?
		else if (isset($_REQUEST[PanicPress::ACTION_APPROVE]) && $this->is_valid_request()) {

			// Authorize this device
			$this->options->authorize($_REQUEST['device_id'])->save();

			echo PanicPress::RESPONSE_SUCCESS . PanicPress::ACTION_APPROVE;
			exit;
		}

		// Is this a valid request to reject authorization?
		else if (isset($_REQUEST[PanicPress::ACTION_REJECT]) && $this->is_valid_request()) {

			// Revoke authorization for this device
			$this->options->reject($_REQUEST['device_id'])->save();

			echo PanicPress::RESPONSE_SUCCESS . PanicPress::ACTION_REJECT;
			exit;
		}

		// Is this a valid request to delete all knowledge of a device?
		else if (isset($_REQUEST[PanicPress::ACTION_DELETE]) && $this->is_valid_request()) {

			// Delete all knowledge of this device
			$this->options->delete($_REQUEST['device_id'])->save();

			echo PanicPress::RESPONSE_SUCCESS . PanicPress::ACTION_DELETE;
			exit;
		}

		// Is this an installation request?
		else if (isset($_REQUEST[PanicPress::ACTION_INSTALL_CHECK])) {

			// Let main server know that plugin is installed and if the API key submitted was correct
			echo PanicPress::RESPONSE_INSTALLED . ($this->is_valid_request() ? "true" : "false");
			exit;
		}

	}

	/**
	 * Is this a valid request?
	 * 
	 * @return boolean
	 */
	private function is_valid_request() {

		// Ensure request came from the actual PanicPress server
		$request_ip	= $_SERVER['REMOTE_ADDR'];
		$parsed_url	= parse_url(PANICPRESS_HOME_URL);
		$real_ip	= gethostbyname($parsed_url['host']);

		if ($request_ip == $real_ip) {

			// Ensure API key sent with request matches one stored in options
			$api_key = isset($_REQUEST['api_key']) ? $_REQUEST['api_key'] : '';

			if ($this->options->api_key == $api_key) {
				return true;
			}
		}

		return false;
	}	
}