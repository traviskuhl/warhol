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

		// yes it's loaded
		$this->_loaded = true;

	}

	public function manifest() {
		return $this->_manifest;
	}

	public function loaded() {
		return $this->_loaded;
	}

	public function fid($path) {
		if ($path{0} != '/') {$path = "/{$path}"; }
		return md5($path);
	}

	public function file($by, $path) {
		// path has a user
		if (substr($path,0,2) === 'u:') {
			return new client\external($this, substr($path,2));
		}	
		$fid = ($by == 'fid' ? $path : $this->fid($path));
		return new client\file($this, $fid);
	}

	// rollup
	public function rollup($name) {
		return new client\rollup($this, $name);
	}

	// val
	public function val($key, $ary, $default=false) {
		return (array_key_exists($key, $ary) ? $ary[$key] : $default);
	}

	// tag
	public function tag($type, $what, $cfg=array()) {
		$lines = array();

		// see if it's a file
		if (is_array($what)) {
			foreach ($what as $item) {
				$lines[] = $this->tag($type, $item, $cfg);
			}
		}
		else {
			$file = $this->file('path', $what);
			$url = $file->http();	

			switch($type) {

				// style tag
				case 'style':
					$lines[] = '<link rel="'.$this->val('rel', $cfg, 'stylesheet').'" type="'.$this->val('type', $cfg, 'text/css').'" href="'.$url.'">';

					break;

				// image tag
				case 'image':


				// script tag
				case 'script':

			};
		}
		return implode("\n", $lines);
	}

}