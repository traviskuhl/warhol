<?php

namespace warhol\cli;
use \warhol;

class cmd {

	// holders
	protected $commands = array();
	protected $options = array();
	protected $manifest = array();
	protected $config = array();

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
			$this->init();
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

	public function process() {
		$args = array();
  		$cmds = $this->commands;

		// reflect on the function
		$ref = new \ReflectionMethod($this, 'run');

		// args
		foreach ($ref->getParameters() as $param) {
			// loop through
			if ($param->name == 'opts') {
				$args[] = $this->options;
			}
			else {
				$c = array_shift($cmds);
				$args[] = ($c ?: $param->getDefaultValue());
			}
		}

		// root
		call_user_func_array(array($this, 'run'), $args);

	}

	public function finfo($file) {
		$file = realpath($file);

		// root
		$root = $this->config->root;

		// get it's ext
		$ext = strtolower(array_pop(explode('.', $file)));

		// get it's relative path
		$rel = str_replace($root, '', $file); $rel = ($rel{0} != '/' ? "/{$rel}" : $rel);

		// content
		$content = file_get_contents($file);

		// get our settings and stuff
		list($settings, $rollups) = $this->getSettings($ext, $content);

		// name
		$name = str_replace(".{$ext}", '', basename($file));

		// return it's info
		return array(
			'id' => md5($rel),
			'rel' => $rel,
			'ext' => $ext,
			'rollups' => $rollups,
			'settings' => $settings,
			'mtime' => filemtime($file),
			'md5' => md5($content),
			'bid' => false,
			'name' => $name,
			'dir' => str_replace("{$name}.{$ext}", '', $rel)
		);

	}

	public function getSettings($ext, $content) {
		if (stripos($content, '@warhol:') === false) {
			return array(array(), array());
		}
		
		// hold for more
		$settings = $rollups = array();

		// check for each setting
		if (preg_match_all('#warhol:([^\s]+) ([^\s]+)#i', $content, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$sname = $match[1];				
				foreach (explode(',', trim($match[2])) as $item) {
					if (stripos($item, ':')===false) { $item .= ':'; }
					list($name, $val) = explode(':', $item);
					$settings[$sname][$name] = $val;
				}
			}
		}

		// rollup
		if (isset($settings['rollup'])) {
			$rollups = $settings['rollup'];
			unset($settings['rollup']);
		}

		// give them back
		return array($settings, $rollups);

	}

	public function getConfig($root=false){ 

		// use cwd
		if (!$root) {$root = realpath(getcwd());}

		while(file_exists($root) AND $root != '/') {
			if (file_exists("$root/.warhol")) {
				break;
			}
			else {
				$root = dirname($root);
			}
		}

		// still no is bad
		if (!file_exists("$root/.warhol/config")) {
			$this->end("Unable to find .warhol folder");
		}

		// json
		$this->config = new warhol\db("$root/.warhol/config");
		$this->manifest = new warhol\db("$root/.warhol/manifest");	

	}

	public function getCommand($idx, $default=false) {
		return (array_key_exists($idx, $this->commands) ? $this->commands[$idx] : $default);
	}

	/// 
	public function setCommands($cmds) {
		$this->commands = $cmds;
		return $this;
	}
	public function setOptions($opts) {
		$this->options = $opts;
		return $this;
	}

	public function ask($text, $default=false, $tf=true) {
		echo "$text ".($default ? "[$default]" : "").": ";
		$a = trim(fgets(STDIN));		
		if (empty($a) OR $a == "") { $a = $default;}
		if ($tf) {
	 		switch(strtolower($a{0})) {
				case 'y': return true;
				case 'n': return false;
				default: return null;
			}
		}
		return $a;
	}

}