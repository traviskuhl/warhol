<?php

namespace warhol;
use \warhol;

class client extends plugin {

	// loaded
	private $_loaded = false;
	private $_manifest = array();
	public $cfg = array();

	// init
	public function init($cfg=array()) {
		$this->cfg = $cfg;

		// manifest
		$file = realpath($cfg['manifest']);

		// lets load our root
		if (!file_exists($file)) {
			return false;
		}

		// root
		$root = (isset($cfg['root']) ? $cfg['root'] : dirname($file));

		// root
		$this->root = realpath($root);

		// manifest
		$this->_manifest = new db($file);		

		// load config if we can find it
		if (file_exists("$root/.warhol/config")) {
			$this->_config = new db("$root/.warhol/config");
		}

		// yes it's loaded
		$this->_loaded = true;

	}

	public function manifest() {
		return $this->_manifest;
	}

	public function config() {
		return $this->_config;
	}

	public function getBid() {		
		return $this->_manifest->get()->get('bid');
	}

	public function loaded() {
		return $this->_loaded;
	}

	public function fid($path) {
		if ($path{0} != '/') {$path = "/{$path}"; }
		return md5($path);
	}

	public function file($by, $path) {
		// external prefix
		$ep = (isset($this->cfg['userPrefix']) ? $this->cfg['userPrefix'] : 'u:' );

		// path has a user
		if ($by == 'path' AND substr($path,0,strlen($ep)) === $ep) {
			return new client\external($this, $path);
		}	
		$fid = (($by == 'fid' OR $by == 'id') ? $path : $this->fid($path));

		return new client\file($this, $fid);
	}

	// rollup
	public function rollup($name) {
		// make sure there's no .
		$name = str_replace(array('.js','.css'), "", $name);

		return new client\rollup($this, $name);
	}

	// val
	public function val($key, $ary, $default=false) {
		return (array_key_exists($key, $ary) ? $ary[$key] : $default);
	}

	// url
	public function tokenize($str) {
		$bid = $this->_manifest->get()->get('bid', time());
		return str_replace(array('{bid}'), array($bid), $str);
	}

	// url
	public function url($path) {
		$file = $this->file('path', $path);
		return $file->http();
	}

	// tag
	public function tag($type, $what, $cfg=array()) {
		$lines = array();

		// dev
		if ($this->cfg['env'] == 'dev') {

			// see if it's a file
			if (is_array($what)) {
				foreach ($what as $item) {
					$lines[] = $this->tag($type, $item, $cfg);
				}
			}
			else {
				// rollup prefix
				if (is_string($what) AND substr($what,0,6) === 'rollup') {
					$r = $this->rollup(substr($what,7));
					foreach ($r->getFiles() as $file) {
						$lines[] = $this->tag($type, $file, $cfg);
					}
				}
				else {
					$file = (is_string($what) ? $this->file('path', $what) : $what);
					$url = $file->http();	
					switch($type) {

						// style tag
						case 'style':
							$lines[] = '<link rel="'.$this->val('rel', $cfg, 'stylesheet').'" type="'.$this->val('type', $cfg, 'text/css').'" href="'.$url.'">';

							break;

						// image tag
						case 'image':

							break;
						// script tag
						case 'script':
							$lines[] = '<script type="text/javascript" src="'.$url.'"></script>';
							break;
					};
				}
			}
		}
		else {



		}


		return implode("\n", $lines);
	}

}