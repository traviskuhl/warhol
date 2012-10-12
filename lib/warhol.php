<?php

/// global namespace to start
namespace {

	// always use UTC
	date_default_timezone_set("UTC");

	// autoloader
	spl_autoload_register(array('warhol', 'autoloader'));

	// we assume we're in the root
	define("W_LIB_ROOT", realpath(__DIR__));


	////////////////////////////////////////////
	/// @class warhol
	///
	/// @brief static warhol class
	////////////////////////////////////////////
	final class warhol {

		// instance
		private static $instance = false;

		////////////////////////////////////////////
		/// @brief auto load any needed classes
		///
		/// @param $name file path
		/// @return void
		////////////////////////////////////////////
		public static function autoloader($name) {

			// get the parts
			$parts = explode('\\', $name);

			// file 
			$file = false;

			// if part1 is tuaurs
			if ($parts[0] == 'warhol') { 
				$file = W_LIB_ROOT . "/" . implode("/", $parts) . ".php";
			}

			// if file include it
			if ($file AND file_exists($file)) {
				include_once($file);
			}

		}

		////////////////////////////////////////////
		/// @brief singleton access to base warhol
		///
		/// @return singleton instance of warhol/base
		////////////////////////////////////////////
		public static function singleton() {
			if (!self::$instance) {
				self::$instance = new warhol\base();
			}
			return self::$instance;
		}

		////////////////////////////////////////////
		/// @brief magic method to forward any static
		///			calls to our singleton instance
		///
		/// @param $name name of method to call
		/// @param $args array of arguments to pass
		/// @return function output from i
		////////////////////////////////////////////
		public static function __callStatic($name, $args) {
			$i = self::singleton();
			return call_user_func_array(array($i, $name), $args);	
		}

		////////////////////////////////////////////
		/// @brief get a variable from instance
		///
		/// @param $name name of variable
		/// @return return value as object || false
		////////////////////////////////////////////
		public static function get($name) {
			$i = self::singleton();
			$v = $i->$name;
			return (is_array($v) ? (object)$v : $v);
		}

	}

}


////////////////////////////////////////////
/// enter warhol namespace for base class
////////////////////////////////////////////
namespace warhol {

	// localize an exception 
	class Exception extends \Exception {}


	////////////////////////////////////////////
	/// @class base
	/// @namespace warhol
	///
	/// @brief main warhol class 
	////////////////////////////////////////////
	class base {

		// our version
		const VERSION = "dev";

		private $manifest = false;
		private $user = array();

		// formators
		protected $formators = array();		

		public function __construct() {

			// see if this user is running as sudo
			// if yes, we really want their user
			if (isset($_SERVER['SUDO_USER'])) {				
				$this->user = array(
					'name' => $_SERVER['SUDO_USER'],
					'uid' => $_SERVER['SUDO_UID'],
					'gid' => $_SERVER['SUDO_GID']
				);
			}
			else if (isset($_SERVER['USER'])) {				
				$this->user = array(
					'name' => $_SERVER['USER'],
					'uid' => posix_getuid(),
					'gid' => posix_getgid()
				);
			}
			
		}

		public function __call($name, $args) {
			if ($this->client AND method_exists($this->client, $name)) {
				return call_user_func_array(array($this->client, $name), $args);
			}
			else if (method_exists($this, $name)) {
				return call_user_func_array(array($this, $name), $args);
			}
			return false;
		}

		public function init() {

			// load any files we definiley need
			$this->load(array(
					W_LIB_ROOT.'/warhol/formator/*.php'
				));

		}

		////////////////////////////////////////////
		/// @brief load files
		///
		/// @return void
		////////////////////////////////////////////
	    public function load($paths) {
	        foreach($paths as $pattern) {

	            // is it a file
	            if (stripos($pattern, '.php') !== false AND stripos($pattern, '*') === false)  {
	                $files = array($pattern);
	            }
	            else {
	                $files = glob($pattern);            
	            }

	            // loop through each file
	            foreach ($files as $file) {           

	                // load it 
	                include_once($file);

	                // loaded
	                $this->loaded[] = $file;

	            }

	        }
	    }

		////////////////////////////////////////////
		/// @brief register a new formator
		///
		/// @param $class formator class
		/// @param $ext array of file exstensions to handle
		/// @return warhol instance
		////////////////////////////////////////////	    
	    public function formator($class, $exts) {
	    	foreach ($exts as $ext) {
	    		$this->formators[$ext] = array(
	    			'class' => $class,
	    			'instance' => false
	    		);
	    	}	    		 
	    	return $this;
	    }
	    public function getFromatorExt() {
	    	return array_keys($this->formators);
	    }
	    public function getFormator($ext) {
	    	// no formator return the false
	    	if (!array_key_exists($ext, $this->formators)) {
	    		return new formator();
	    	}
	    	if (!$this->formators[$ext]['instance']) {
	    		$this->formators[$ext]['instance'] = new $this->formators[$ext]['class'];
	    	}
	    	return $this->formators[$ext]['instance'];
	    }

		////////////////////////////////////////////
		/// @brief create a cli instance and 
		///			run the argument processor
		///
		/// @return void
		////////////////////////////////////////////
		public function cli() {
			$cli = new cli($this);
			$cli->run();
		}

		////////////////////////////////////////////
		/// @brief create a server instance 
		///
		/// @return server instance
		////////////////////////////////////////////
		public function server($cfg) {
			// no need to call anything else, server always serves
			return new server($this, $cfg);			
		}

		////////////////////////////////////////////
		/// @brief create a client
		///
		/// @return client instnace
		////////////////////////////////////////////
		public function client($cfg) {
			// no need to call anything else, server always serves
			$this->client = new client($this, $cfg);						
			return $this;
		}


		public function getUser() {
			return $this->user;
		}
		
	}


}