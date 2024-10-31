<?php

class PanicPress_Model_Options {

	/**
	 * The name used to store this plugin's options.
	 */
	const OptionName = 'panicpress_options';

	/**
	 * @var array A list of authorized device IDs.
	 */
	public $authorized = array();

	/**
	 * @var array A list of rejected device IDs.
	 */
	public $rejected = array();

	/**
	 * @var boolean API key used to interact with server.
	 */
	public $api_key = false;

	/**
	 * @var boolean If we should shutdown the blog.
	 */
	public $shutdown = false;

	/**
	 * Constructor.
	 */
	public function __construct() {

		// If options haven't been set yet
		if (false === $options = get_option(self::OptionName)) {

			$options = $this->createDefaultOptions();
		}

		// Apply options to self
		$this->authorized 	= $options['authorized'];
		$this->rejected 	= $options['rejected'];
		$this->api_key		= $options['api_key'];
		$this->shutdown 	= $options['shutdown'];
	}

	/**
	 * Set a collection of options and chain.
	 * 
	 * @param array $options The options to set.
	 *
	 * @return PanicPress_Model_Options
	 */
	public function set($options) {
		foreach ($options as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}

		return $this;
	}

	/**
	 * Authorize a device.
	 * 
	 * @param  string $device_id The ID of the device to authorize.
	 * 
	 * @return PanicPress_Model_Options
	 */
	public function authorize($device_id) {

		$this->delete($device_id);

		$this->authorized[] = $device_id;

		return $this;
	}

	/**
	 * Rejects authorization for a device.
	 * 
	 * @param  string $device_id The ID of the device to authorize.
	 * 
	 * @return PanicPress_Model_Options
	 */
	public function reject($device_id) {

		$this->delete($device_id);

		$this->rejected[] = $device_id;

		return $this;
	}

	/**
	 * Deletes all knowledge of a device from approved and rejected lists.
	 * 
	 * @param  string $device_id The ID of the device to delete all knowledge of.
	 * 
	 * @return PanicPress_Model_Options
	 */
	public function delete($device_id) {

		if (is_array($this->authorized) && false !== array_search($device_id, $this->authorized)) {
			array_splice($this->authorized, array_search($device_id, $this->authorized), 1);
		}

		if (is_array($this->rejected) && false !== array_search($device_id, $this->rejected)) {
			array_splice($this->rejected, array_search($device_id, $this->rejected), 1);
		}

		return $this;
	}

	/**
	 * If a device is authorized.
	 * 
	 * @param  string $device_id The ID of the device to check.
	 * 
	 * @return boolean
	 */
	public function is_authorized($device_id) {
		return isset($this->authorized) && in_array($device_id, $this->authorized);
	}

	/**
	 * If a device has had its authorization rejected.
	 * 
	 * @param  string $device_id The ID of the device to check.
	 * 
	 * @return boolean
	 */
	public function is_rejected($device_id) {
		return isset($this->rejected) && in_array($device_id, $this->rejected);
	}

	/**
	 * Save this plugin's options.
	 * 
	 * @return PanicPress_Model_Options
	 */
	public function save() {
		update_option(self::OptionName, array(
			'authorized' 	=> $this->authorized,
			'rejected'		=> $this->rejected,
			'api_key'		=> $this->api_key,
			'shutdown'		=> $this->shutdown,
		));

		return $this;
	}

	/**
	 * Create default options for this plugin and save them.
	 *
	 * (Yeah, there's probably "too much" DB access here, but it shoul only happens once.)
	 * 
	 * @return array
	 */
	private function createDefaultOptions() {
		// First, ensure the option exists (don't autoload)
		add_option(self::OptionName, array(), '', 'no');

		// Save options
		$this->save();

		// Return the created options
		return get_option(self::OptionName);
	}
}