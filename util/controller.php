<?php
	class PanicPress_Util_Controller {

		/**
		 * Load and return a view.
		 * 
		 * @param string $name The name of the view to load.
		 * @param mixed  $vars The variables to pass along to the view, if any.
		 * 
		 * @return PanicPress_Util_View
		 */
		protected function getView($name, $vars = null) {
			return PanicPress_Util_View::factory(PANICPRESS_VIEWS . "/$name", $vars);
		}
	}