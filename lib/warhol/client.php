<?php

namespace warhol;
use \warhol;

class client extends plugin {

	// loaded
	private $_loaded = false;	
	private $_manifest = array();

	// init
	public function init($cfg=array()) {		

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

	public function getFile($path) {
		$fid = $this->fid($path);
		return new client\file($this, $fid);
	}

}