<?php
	class PanicPress_Util_View {

	 	/**
	 	 * The default extension for template files.
	 	 */
	 	const default_extension = ".php";

		/**
		 * @var string The template file to use as a source.
		 */
		private $file = '';

		/**
		 * @var array View variables used to render the template.
		 */
	 	private $vars = array();

	 	/**
	 	 * The template constructor.
	 	 * 
	 	 * @param string $file The template file to use as a source.
	 	 * @param array  $vars The view variables used to render the template.
	 	 */
	 	public function __construct($file = null, $vars = null) {
	 		$this->init($file, $vars);
	 	}

	 	/**
	 	 * Get a view variable.
	 	 * 
	 	 * @param  string $name The name of ther variable to get.
	 	 * 
	 	 * @return mixed
	 	 */
	  	public function __get($name) {
	    	return $this->vars[$name];
	  	}
	 
	 	/**
	 	 * Set a view variable.
	 	 * 
	 	 * @param string $name  The name of the view variable to set.
	 	 * @param mixed  $value The value to set.
	 	 */
	  	public function __set($name, $value) {
	    	$this->vars[$name] = $value;
	  	}

	  	/**
	  	 * Renders the template and return the rendered text.
	  	 * 
	  	 * @return string
	  	 */
	  	public function __toString() {
	  		return $this->render();
	  	}

	 	/**
	 	 * Render the template and return the rendered text.
	 	 * 
	 	 * @param string $file The template file to use as a source.
	 	 * @param array  $vars The view variables used to render the template.
	 	 *
	 	 * @return string
	 	 */
	  	public function render($file = null, $vars = null) {
	  		$this->init($file, $vars);

	  		// Have to have a template file
	  		if (is_null($this->file) || 0 === strlen($this->file) || ! is_file($this->file)) {
	  			echo "Must supply a valid template filename.<br><pre>";

	  			// Only show backtrace to admin-level users
	  			if (is_admin()) {
	  				debug_print_backtrace();
	  			}

	  			echo "</pre>";
	  			return '';
	  		}

	    	extract($this->vars);

	    	ob_start();
	    	include($this->file);
	    	return ob_get_clean();
	  	}		

	  	/**
	  	 * Initialize filename and vars.
	 	 * 
	 	 * @param string $file The template file to use as a source.
	 	 * @param array  $vars The view variables used to render the template.
	 	 * 
	  	 * @return void
	  	 */
	  	private function init($file, $vars) {
	 		if ( ! is_null($file)) {

	 			if (is_file($file)) {
	 				$this->file = $file;
	 			}
	 			// If the provided file doesn't exist, try appendindng the default extension
	 			else {
	 				$this->file = is_file($file . self::default_extension) ? $file . self::default_extension : $file;
	 			}
	 		}

	 		if ( ! is_null($vars)) {
	 			if (is_array($vars)) {
	 				$this->vars = $vars;
	 			} else {
	 				throw new Exception("Vars (2nd parameter) must be passed as an array.");
	 			}
	 		}
	  	}

	  	/**
	  	 * Creates and returns a PanicPress_Util_View, ready for use!
	 	 * 
	 	 * @param string $file The template file to use as a source.
	 	 * @param array  $vars The view variables used to render the template.
	 	 * 
	  	 * @return PanicPress_Util_View
	  	 */
	  	public static function factory($file, $vars = null) {
	  		return new PanicPress_Util_View($file, $vars);
	  	}
	}