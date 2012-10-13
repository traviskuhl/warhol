<?php

namespace warhol;
use \warhol;

abstract class formator {
	
	private $_manifest;
	private $_config;

	// set and get
	final public function setManifest($manifest) {
		$this->_manifest = $manifest;
	}
	final public function setConfig($config) {
		$this->_config = $config;
	}
	final protected function getManifest() {
		return $this->_manifest;
	}
	final protected function getConfig() {
		return $this->_config;
	}

	/// what they need
	abstract public function format($body, $file);

}