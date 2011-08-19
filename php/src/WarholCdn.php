<?php

class Warhol {

	// our type ids
	const TYPE_CSS = 1;
	const TYPE_JS = 2;
	const TYPE_PNG = 3;
	const TYPE_GIF = 4;
	const TYPE_JPG = 5;

	// type id info
	public static $TYPES = array(
		self::TYPE_CSS 	=> array("name" => "css", "ext" => ".css", "mime" => "text/css", "group" => "style"),
		self::TYPE_JS 	=> array("name" => "js", "ext" => ".js", "mime" => "text/javascript", "group" => "javascript"),
		self::TYPE_PNG 	=> array("name" => "png", "ext" => ".png", "mime" => "image/png", "group" => "images"),	
		self::TYPE_GIF 	=> array("name" => "gif", "ext" => ".gif", "mime" => "image/gif", "group" => "images"),	
		self::TYPE_JPG 	=> array("name" => "jpg", "ext" => ".jpg", "mime" => "image/jpg", "group" => "images"),		
	);

	///
	/// @brief 
	///	
	public static function init($config) {
		
	
	}


	///
	/// @brief 
	///		
	public static function parse($args) {
		
		// file 
		$manifest = false;
		
		// did they give us the conents of the file
		if (isset($args['content'])) {
			$manifest = $args['conent'];
		}
		
		// did they give us a file
		if (isset($args['file'])) {
			$manifest = file_get_contents($args['file']);
		}
		
		// they gave a root dir
		if (isset($args['dir'])) {
			$manifest = file_get_contents("{$args['dir']}/.warhol/manifest");
		}
	
		// else stop
		if (!$manifest) {
			throw new Exception("No manifest given"); return;
		}
		
		// parse it 
		return new Warhol_Manifest(json_decode($manifest, true));

	
	}

}

class Warhol_Manifest {
	
	// 
	private $_style = array();
	private $_javascript = array();
	private $_images = array();
	private $_manifest = false;
	private $_http = false;
	private $_https = false;

	public function __construct($manifest) {
	
		// globalize manifest
		$this->_manifest = $manifest;
		
		// globalize some config stuff
		$this->_http = $manifest['config']['root']['http'];
		$this->_https = $manifest['config']['root']['https'];
		
		// lets loop through and 
		foreach ($manifest['files'] as $file) {
			switch($file['type']['id']) {
			
				// css
				case Warhol::TYPE_CSS:
					$this->_style[$file['id']] = new Warhol_Manifest_Item($file, $this); break; 
					
				// js
				case Warhol::TYPE_JS:
					$this->_javascript[$file['id']] = new Warhol_Manifest_Item($file, $this); break;
					
				
				// image
				case Warhol::TYPE_PNG:
				case Warhol::TYPE_JPG:
				case Warhol::TYPE_GIF:
					$this->_images[$file['id']] = new Warhol_Manifest_Item($file, $this); break;
					
				// unknow
				default:
					continue;					
			};
		}
	
	}
	
	public function getManifest(){ 
		return $this->_manifest;
	}
	
	public function style() {	
		return $this->_style;
	}
	
	public function javascript() {
	
	}
	
	public function images() {
	}
	
	public function combo($type=false, $ssl=false) {
		$items = ($type == TYPE_JS ? $this->_javascript : $this->_style );
		return $this->getUrl(array(
			'root' => "combo",
			'paths' => array_map(function($item){ return $item['path']; }, $items);
		), $ssl);		
	}
	
	public function getUrl($arg, $ssl=false) {
		$root = ($ssl ? "https://{$this->_https}" : "http://{$this->_http}" );
		if ( is_string($arg) ) {
			return $root . trim($arg,'/');	
		}
		else if (is_array($arg)) {
			return $root . $arg['root'] . "?" . implode("&", $arg['paths']);
		}
	}

}

class Warhol_Manifest_Item {

	// holders
	private $_item = false;
	private $_manifest = false;

	public function __construct($item, $manifest) {
		$this->_item = $item;	
		$this->_manifest = $manifest;
	}
	
	public function __get($name) {
		return array_key_exists($name, $this->_item) ? $this->_item[$name] : false;
	}
	
	public function getUrl($ssl=false) {
		
		// generate a url
		return $this->_manifest->getUrl($this->path, $ssl);
			
	}

}


?>