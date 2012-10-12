<?php

namespace warhol;
use \warhol;

class plugin {

	// base
	protected $base;
	
	////////////////////////////////////////////
	/// @brief construct a new plugin class
	///			extensions will be called with init
	///
	/// @return void
	////////////////////////////////////////////
	public function __construct($base) {
		$this->base = $base;

		// if we have an init class defined
		// by the plugin, call it now
		if (method_exists($this, 'init')) {
			$args = func_get_args(); array_shift($args);
			call_user_func_array(array($this, 'init'), $args);
		}

	}

	////////////////////////////////////////////
	/// @brief any methods called that aren't 
	///			defined are passed to base	
	///
	/// @param $name name of function
	/// @param $args array of arguments
	/// @return result || false
	////////////////////////////////////////////
	public function __call($name, $args=array()) {
		if (method_exists($this, $name)) {
			return call_user_func_array(array($this, $name), $args);
		}
		else if (method_exists($this->base, $name)) {
			return call_user_func_array(array($this->base, $name), $args);
		}
		return false;
	}

	public function __get($name) {
		return $this->base->{$name};
	}

}