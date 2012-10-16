<?php

namespace warhol;

class db {

	private $_data = array();
	private $_file = false;

	private $change = false;

	public function __construct($file) {

		$this->_file = $file;

		// no file exists
		if (file_exists($this->_file)) {
			$this->_data = json_decode(file_get_contents($this->_file), true);
		}

	}

	public function getFile(){
		return $this->_file;
	}

	public function __destruct() {
		if ($this->change) {
			file_put_contents($this->_file, json_encode($this->_data));
		}
	}

	public function __get($name) {
		return $this->get('default', $name);
	}

	public function __set($name, $value) {
		return $this->set($name, $value);
	}

	public function get($ns='default', $name=false) {
		$ns = new \taurus\db\item($ns, $this);
		return ($name ? $ns->get($name) : $ns);
	}

	public function set($name, $value=false, $ns='default') {
		if (is_array($name)) {
			foreach ($name as $k => $v) {
				$this->set($k, $v, $ns);
			}
			return;
		}
		return $this->get($ns)->set($name, $value);
	}

	public function getData() {
		return $this->_data;
	}

	public function setByNamespace($ns, $key, $val) {
		if (!array_key_exists($ns, $this->_data)) { $this->_data[$ns] = array(); }
		$this->_data[$ns][$key] = $val;
		$this->change = true;
		return $this;
	}

	public function getByNamespace($ns) {
		if (!array_key_exists($ns, $this->_data)) { $this->_data[$ns] = array(); }
		return $this->_data[$ns];
	}


}


namespace taurus\db;

class item {
	private $root;
	private $parent;
	public function __construct($root, $parent) {
		$this->root = $root;
		$this->parent = $parent;
	}

	// all
	public function all() {
		return $this->parent->getByNamespace($this->root);
	}

	public function __set($name, $value) {
		return $this->set($name, $value);
	}

	public function __get($name) {
		return $this->get($name);
	}

	public function get($name, $default=false) {
		$d = $this->parent->getByNamespace($this->root);
		return (array_key_exists($name, $d) ? $d[$name] : $default);
	}

	public function set($name, $value=false) {
		if (!is_array($name) AND $value != false) {
			$name = array($name=>$value);
		}
		foreach ($name as $key => $value) {
			$this->parent->setByNamespace($this->root, $key, $value);
		}
		return $this;
	}

	public function exists($name) {
		$d = $this->parent->getByNamespace($this->root);
		return array_key_exists($name, $d);
	}

}