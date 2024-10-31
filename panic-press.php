<?php
	/*
	Plugin Name: PanicPress
	Plugin URI: http://panic-press.com/
	Description: Two-factor authentication for your blog.  Before anyone unfamiliar can access your administration section, you must approve them through text message.  If you fear your blog has been hacked?  Simply text "panic" to a special phone number and your whole blog administration shuts down!
	Version: 1.0
	Author: Dan Hulton
	Author URI: http://blog.danhulton.com
	License: Copyright 2012, Dan Hulton.

	Copyright 2012 Dan Hulton, dan@danhulton.com

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/

	define("IS_DEV", "http://wp.dev" == site_url());

	// Version of PanicPress Plugin/API
	define('PANICPRESS_PLUGIN_VERSION', '1.0');
	define('PANICPRESS_API_VERSION', '1.0');

	// If the plugin is installed as a must-use plugin (better for security)
	define('INSTALLED_AS_MUST_USE', strpos(__FILE__, 'mu-plugins') !== false);

	// Location of plugin
	define('PANICPRESS_DIR', 			(INSTALLED_AS_MUST_USE ? WPMU_PLUGIN_DIR : WP_PLUGIN_DIR) . '/panic-press');

	// Location of important directories
	define('PANICPRESS_MODELS',			PANICPRESS_DIR . '/model');
	define('PANICPRESS_CONTROLLERS',	PANICPRESS_DIR . '/controller');
	define('PANICPRESS_VIEWS', 			PANICPRESS_DIR . '/view');
	define('PANICPRESS_UTILS',			PANICPRESS_DIR . '/util');

	// Main server URLs
	define('PANICPRESS_HOME_URL',		IS_DEV ? 'http://www.panic-press.dev/' : 'http://www.panic-press.com/');
	define('PANICPRESS_SECURE_URL',		IS_DEV ? 'https://www.panic-press.dev/' : 'https://www.panic-press.com/');
	define('PANICPRESS_API_URL',		PANICPRESS_HOME_URL . 'api/' . PANICPRESS_API_VERSION . '/');

	class PanicPress {

		/**
		 * Request blog admin section be shut down.
		 */
		const ACTION_PANIC = 'panic';

		/**
		 * Request blog admin section be restored.
		 */
		const ACTION_SAFE = 'safe';

		/**
		 * Authorize a device.
		 */
		const ACTION_APPROVE = 'approve';

		/**
		 * Reject authorization for a device.
		 */
		const ACTION_REJECT = 'reject';

		/**
		 * Delete all mention of a device.
		 */
		const ACTION_DELETE = 'delete';

		/**
		 * Check and see if plugin is correctly installed.
		 */
		const ACTION_INSTALL_CHECK = 'install_check';

		/**
		 * Action successfully carried out blog-side.
		 */
		const RESPONSE_SUCCESS = 'success-';

		/**
		 *  Response we expect to get if PanicPress plugin is installed.
		 */
		const RESPONSE_INSTALLED = "ppp_installed-";

		/**
		 * Success message received if blog has a valid subscription.
		 */
		const VALID_SUBSCRIPTION = "valid-subscription";

		/**
		 * Name of the cookie containing the saved ID of this device.
		 */
		const COOKIE_DEVICE_ID = "panicpress_device_id";

		/**
		 * Name of the cookie containing the saved name of this device.
		 */
		const COOKIE_DEVICE_NAME = "panicpress_device_name";

		/**
		 * Wait fifteen minutes before requesting authorization again - something's up if it's been this long.
		 */
		const AUTH_REQUEST_DELAY = 900;

		/**
		 * Constructor.
		 */
		public function __construct() {

			// Load plugin options
			require_once(PANICPRESS_MODELS . '/options.php');
			$options = new PanicPress_Model_Options;

			// Utilities
			require_once(PANICPRESS_UTILS . '/controller.php');
			require_once(PANICPRESS_UTILS . '/view.php');

			// Admin-side controllers
			if (is_admin()) {

				// First, make sure blog hasn't been deactivated
				if ($options->shutdown) {
					shutItDown();
					exit;
				}

				// Enable admin security
				require_once(PANICPRESS_CONTROLLERS . '/admin/init.php');
				$controller_admin_init = new PanicPress_Controller_Admin_Init($options);

				// Add backend menus
				require_once(PANICPRESS_CONTROLLERS . '/admin/options.php');
				$controller_admin_options = new PanicPress_Controller_Admin_Options($options); 
			}

			// Reader-side controllers
			else {
				require_once(PANICPRESS_CONTROLLERS . '/request.php');
				$controller_request = new PanicPress_Controller_Request($options);
			}

			register_uninstall_hook(__FILE__, "PanicPress::uninstall");
		}

		/**
		 * If there's an active PanicPress subscrption for this blog.
		 * 
		 * @return boolean
		 */
		public static function has_active_subscription($api_key) {

			// Plugin must be set up first with an API key
			if ( ! $api_key) {
				return false;
			}

			try {
				// Check subscription with main server
				$response = self::api('subscription', array('body' => array('api_key' => $api_key)));
			}
			catch (Exception $e) {
				return false;
			}

			// Hey, you need to confirm your contact information!
			if ($response['response']['code'] == 403) {

				// Add notice about contact information			
				wp_enqueue_style("panicpress-admin", plugins_url('/css/admin.css', PANICPRESS_DIR . '/panic-press.php'));
				add_action('admin_notices', "PanicPress::not_confirmed_notice");
			}

			return ($response['response']['code'] == 200 && $response['body'] == self::VALID_SUBSCRIPTION);
		}

		/**
		 * Sends an API call.
		 * 
		 * @param string $action The api action to take.
		 * @param array  $data   The data to pass along.
		 *
		 * @throws Exception
		 * 
		 * @return array 
		 */
		public static function api($action, $data) {

			$data['body']['plugin_version'] = PANICPRESS_PLUGIN_VERSION;

			$response = wp_remote_post(PANICPRESS_API_URL . $action, $data);

			if (is_wp_error($response)) {
				throw new Exception($response->get_error_message());
			}

			return $response;
		}

		/**
		 * Runs on uninstall of plugin.
		 * 
		 * @return void
		 */
		public static function uninstall() {
			delete_option(PanicPress_Model_Options::OptionName);
		}

		/**
		 * Display a notice to confirm your contact information.
		 * 
		 * @return void
		 */
		public static function not_confirmed_notice() {

			$as_parts = parse_url(site_url('/'));

			echo PanicPress_Util_View::factory(PANICPRESS_VIEWS . "/not-confirmed-notice");
		}

		/**
		 * Emit what looks like a PHP error and cease execution.
		 * 
		 * @return void
		 */
		public static function shutItDown() {
			echo "Parse error: parse error, expecting `','' or `';'' in /web/wordpress/wp-begin.php on line 23 ";
			exit;
		}
	}

	$panicpress = new PanicPress;