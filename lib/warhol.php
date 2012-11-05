<?php

/// global namespace to start
namespace {

	// always use UTC
	date_default_timezone_set("UTC");

	// autoloader
	spl_autoload_register(array('warhol', 'autoloader'));

	// we assume we're in the root
	if (!defined('WARHOL_LIB_ROOT')) {
		define("WARHOL_LIB_ROOT", realpath(__DIR__));
	}


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
				$file = WARHOL_LIB_ROOT . "/" . implode("/", $parts) . ".php";
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
		private $client = false;
		private $user = array();

		private $_init = false;

		// formators
		protected $formators = array();

		public function __construct() {

			// see if this user is running as sudo
			// if yes, we really want their user
			if (isset($_SERVER['SUDO_USER'])) {
				$this->user = array(
					'home' => "/home/".$_SERVER['SUDO_USER'],
					'name' => $_SERVER['SUDO_USER'],
					'uid' => $_SERVER['SUDO_UID'],
					'gid' => $_SERVER['SUDO_GID']
				);
			}
			else if (isset($_SERVER['USER'])) {
				$this->user = array(
					'home' => $_SERVER['HOME'],
					'name' => $_SERVER['USER'],
					'uid' => getmyuid(),
					'gid' => getmygid()
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

			// only init once
			if ($this->_init) {return;}
			
			// we are in a phar, so glob does not work
			// need to use directory it to get our files
			foreach (new DirectoryIterator(WARHOL_LIB_ROOT.'/warhol/formator/') as $file) {
				$this->load($file->getPathname());
			}

			// init
			$this->_init = true;

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
		/// @param $name name of formator
		/// @param $class formator class
		/// @param $ext array of file exstensions to handle
		/// @return warhol instance
		////////////////////////////////////////////
	    public function formator($name, $class, $exts, $weight=1) {
	    	foreach ($exts as $ext) {
	    		$this->formators[$name] = array(
	    			'class' => $class,
	    			'instance' => false,
	    			'ext' => $exts,
	    			'weight' => $weight
	    		);
	    	}
	    	return $this;
	    }
	    public function getFromatorExt() {
	    	$ext = array();
	    	foreach ($this->formators as $f) {
	    		$ext = array_merge($ext, $f['ext']);
	    	}
	    	return $ext;
	    }
	    public function getFormators($by, $what) {
	    	$formators = array();
	    	if ($by == 'name') {
	    		foreach ($what as $name => $x) {
	    			$formators[$name] = $x;
	    		}
	    	}
	    	else {
	    		foreach ($this->formators as $name => $f) {
	    			if (in_array($what, $f['ext'])) {
	    				$formators[$name] = $f['weight'];
	    			}
	    		}	    		
	    	}

	    	// arsort
	    	asort($formators);

	    	// loop
	    	foreach ($formators as $name => $i) {
		    	// no formator return the false
		    	if (!array_key_exists($name, $this->formators)) {
		    		unset($formators[$name]);
		    		continue;
	    		}
		    	if (!$this->formators[$name]['instance']) {
		    		$this->formators[$name]['instance'] = new $this->formators[$name]['class'];
		    	}
		    	$formators[$name] = $this->formators[$name]['instance'];
		    }

		    return $formators;
	    }

		////////////////////////////////////////////
		/// @brief create a cli instance and
		///			run the argument processor
		///
		/// @return void
		////////////////////////////////////////////
		public function cli() {
			$this->init();
			$cli = new cli($this);
			$cli->run();
		}

		////////////////////////////////////////////
		/// @brief create a server instance
		///
		/// @return server instance
		////////////////////////////////////////////
		public function server($cfg) {
			$this->init();
			// no need to call anything else, server always serves
			return new server($this, $cfg);
		}

		////////////////////////////////////////////
		/// @brief create a client
		///
		/// @return client instnace
		////////////////////////////////////////////
		public function client($cfg=false) {
			// always init
			$this->init();

			// do we already have a client
			if ($this->client && !$cfg) {
				return $this->client;
			}
			// no need to call anything else, server always serves
			$this->client = new client($this, $cfg);
			return $this;
		}


		public function getUser($key=false) {
			return ($key ? $this->user[$key] : $this->user);
		}

		public function curl($url, $params=array(), $method="GET") {
	        // create a new cURL resource
	        $ch = curl_init();

	        // url is rel
	       	if ($method == 'GET') {
	            $url .= '?'.http_build_query($params);
	        }

	        // set URL and other appropriate options
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_HEADER, false);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

	        if ($method == 'POST') {
	            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	        }

	        // grab URL and pass it to the browser
	        $resp = curl_exec($ch);

	        $i = curl_getinfo($ch);

	        // close cURL resource, and free up system resources
	        curl_close($ch);

	        if ($i['content_type'] == 'text/javascript') {
	        	$resp = json_decode($resp, true);
	        }

	        return array($resp, $i);


		}


	}


}