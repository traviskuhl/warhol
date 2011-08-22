<?php


class WarholCdn {

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
	
	// 
	private $_style = array();
	private $_javascript = array();
	private $_images = array();
	private $_manifest = false;
	private $_files = array();
	private $_config = array();
	
	// stuff we need
	private $_http = false;
	private $_https = false;		
	
	// vars
	private $key, $secret, $oauth, $cache, $local, $ssl = false;
	private $host = "warholcdn.com";

	// singleton
	private static $_singleton = false;

	///
	/// @brief 
	///	
	public function __construct($args=false) {
		
		// api key and secret
		foreach($args as $key => $val) {
			if (property_exists($this, $key) AND $key{0} != "_") {
				$this->$key = $val;
			}
		}
		
		// file 
		$manifest = $config = false;
			
			// they gave a root dir
			if (isset($args['folder'])) {
			
				// set manifest
				$this->setManifest(file_get_contents("{$args['folder']}/.warhol/manifest"));
				
				// read in config
				$this->setConfig(json_decode(file_get_contents("{$args['folder']}/.warhol/config"), true));
				
			}				
		
		// oauth consumer
		if ($this->key AND $this->secret) {
			$this->oauth = new OAuth($this->key, $this->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);		
		}
	
	}
	
	
	///
	/// @brief __call
	///
	public function __call($name, $args) {
		
		// map name
		$name = "get".ucfirst($name);
		
		// do we have a manifest
		if (isset($this->_manifest)) {
			return call_user_func_array(array($this, $name), $args);
		}
	
		// go get our latest build
		$build = $this->request("build/latest");
	
		// config
		$this->setConfig($build['response']['config']);
	
		// now build our manifest
		$this->setManifest($build['response']['build']['manifest']);
		
		// make our call
		return call_user_func_array(array($this, $name), $args);
	
	}

	///
	/// @brief send request to server
	///
	private function request($uri, $params=array(), $method="GET") {
	
		// set our token
		$this->oauth->setToken(md5($this->key.$this->secret), $this->secret);
	
		// fetch
		try {
			$this->oauth->fetch("https://{$this->host}/api/v1/{$uri}", $params, $method);
		} catch(OAuthException $E) {		
			die($E->getMessage());
		}
		
		// not the 200 we want w eshould stop
		$resp = $this->oauth->getLastResponseInfo();
			
			// not 200
			if ( $resp['http_code'] != 200 ) {
				die("Bad response");
			}
		
		// give json response
		return json_decode($this->oauth->getLastResponse(), true);
	
	}
	

	///
	/// @brief get a fileid from a path
	///
	public static function getFileId($path) {
		return md5("/".trim($path,'/'));
	}

	///
	/// @brief parse a manifest file and set it as the 
	///			current manifest object
	///
	public function setManifest($manifest) {
	
		// globalize manifest
		$this->_manifest = $manifest = (is_string($manifest) ? json_decode($manifest, true) : $manifest);		
		
		// lets loop through and 
		foreach ($manifest['files'] as $file) {		
			$this->_files[$file['id']] = new Warhol_Manifest_Item($file, $this);		
			switch($file['type']['id']) {
			
				// css
				case WarholCdn::TYPE_CSS:
					$this->_style[$file['id']] = $this->_files[$file['id']]; break; 
					
				// js
				case WarholCdn::TYPE_JS:
					$this->_javascript[$file['id']] = $this->_files[$file['id']]; break;
					
				
				// image
				case WarholCdn::TYPE_PNG:
				case WarholCdn::TYPE_JPG:
				case WarholCdn::TYPE_GIF:
					$this->_images[$file['id']] = $this->_files[$file['id']]; break;
					
				// unknow
				default:
					continue;					
			};
		}
	
	}
	
	///
	/// @brief set config
	///
	public function setConfig($config) {
	
		// globalize some config stuff
		$config['_http'] = $config['root']['http'];
		$config['_https'] = $config['root']['https'];	
	
		// local
		$config['local'] = $this->local;
		$config['ssl'] = $this->ssl;
	
		// set the config
		$this->_config = $config;
	
	}	
	
	///
	/// @brief get a config variable
	///	
	public function config($name, $default=false) {
		return (array_key_exists($name, $this->_config) ? $this->_config[$name] : $default);
	}
	
	
	///
	/// @brief get a file from the manifest
	///	
	public function getFile($fid) {	
		return (array_key_exists($fid, $this->_files) ? $this->_files[$fid] : false );
	}
	

	///
	/// @brief get style objects
	///	
	public function getStyle() {	
		return $this->_style;
	}
	
	
	///
	/// @brief
	///	
	public function getJavascript() {
		return $this->_javascript;
	}
	
	public function getImages() {
		return $this->_images;
	}
	
	public function getComboUrl($type, $args=array()) {	
		
		// type is an array
		if (is_array($type)) {
			$args['append'] = $type;
			$items = array();
		}
		else {
			$items = ($type == 'javascript' ? $this->_javascript : $this->_style );		
		}
		
		// local
		$local = $this->config('local');
		
		// append files to the items
		if ( isset($args['append']) ) {
			$items = array_merge($items, array_map(function($file){ $o = new StdClass(); $o->path = $file; $o->static = array('path' => $file); return $o; }, $args['append']));
		}
		
		// return the url
		return $this->getUrl(array(
			'path' => "combo",
			'query' => array_map(function($item) use ($local) { return ($local ? $item->static['path'] : $item->path); }, $items),
			'base' => (isset($args['base']) ? $args['base'] : false)
		));		
		
	}

	public function getRollupUrl($name) {
		return $this->getUrl("rollup/{$name}", $base);		
	}
	
	public function getImageUrl($path, $cmds=array()) {
		return $this->getUrl(array(
			"path" => "image/{$path}",
			'query' => array_map(function($k,$v){ return "{$k}=".urlencode($v); }, array_keys($cmds), $cmds), 
			'base' => $base
		));
	}
	
	public function getUrl($arg) {
		if ( is_string($arg) ) {
			return new Warhol_Manifest_Item_Url($arg, false, $this);
		}
		else if (is_array($arg)) {
			return new Warhol_Manifest_Item_Url($arg['path'], $arg['query'], $this);
		}
	}
	
	public function folder($str) {
		return "/".trim($str, "/")."/";
	}

}

class Warhol_Manifest_Item_Url {
	private $host, $path, $query, $scheme;
	public function __construct($path, $query=array(), $manifest) {		
		$local = $manifest->config('local');
		$ssl = ($manifest->config('ssl') ? "_https" : "_http");
		$this->host = rtrim(($local ? $local : $manifest->config($ssl)),"/");		
		$this->scheme = ($local ? "" : ($ssl ? "https://" :"http://"));
		$this->path = trim($path,'/');
		$this->query = implode("&", $query);
		$this->manifest = $manifest;
	}
	public function __toString() {
		return $this->url();
	}

	public function url() {
		return "{$this->scheme}".rtrim($this->host,'/')."/{$this->path}~{$this->query}";
	}

	public function host($host) {
		if ($host == 'https') {		
			$this->host = $this->manifest->config('_https');
			$this->scheme("https");
		}
		else if ($host=='http') {
			$this->host = $this->manifest->config('_http');
		}
		else {
			$this->host = $host;		
		}
		return $this;
	}
	public function scheme($scheme) {
		$this->scheme = $scheme."://";
		return $this;
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
	
	public function getImageUrl($cmds=array(), $ssl) {
		return $this->_manifest->getImageUrl($this->path, $cmds, $ssl);
	}

}

class Warhol_Server {

	///
	/// @brief server
	/// 
	public static function run($folder) {

		// get the manifest from the folder
		$w = new WarholCdn(array('folder'=>$folder));

		
		// path and query
		list($_path, $_query) = explode("~", $_SERVER['PATH_INFO']);
		
		// paht info
		$path = explode("/", trim($_path,'/'));
		$query = explode('&', ($_query));

		// content
		$content = array();
		$mime = false;

		// what's in the path
		switch(array_pop($path)) {
		
			// combo
			case 'combo':
								
				// loop through and get them
				foreach ($query as $item) {
					if (strpos($item,'=') !== false) { continue; }
					$fp = $folder . "/" . trim($item,"/");
				
					// file is local
					if (file_exists($fp)) {					
						$content[] = file_get_contents($fp);
					}				
					$mime = (strpos($item,".css")!==false ? $w::TYPE_CSS : $w::TYPE_JS);
				}
			
				// break
				break;
			
			// rollup
			case 'rollup':
			
				// rollups
				$rollups = $w->rollups;
					
					// no rollups
					if (!$rollups) { die; }
					
				// name
				$name = $path[1];		
				
					
				// rollup
				$rollup = array();	
							
				// find the rollup in the manifest
				if (isset($rollups['style']) AND array_key_exists($name, $rollups['style'])) {
					$rollup = $rollups['style'][$name];
					$mime = $w::TYPE_CSS;
				}

				if (isset($rollups['javascript']) AND  array_key_exists($name, $rollups['javascript'])) {
					$rollup = $rollups['javascript'][$name];
					$mime = $w::TYPE_JS;
				}
			
				foreach ($rollup as $fid => $x) {
					$file = $manifest->getFile($fid);										
					$content[] = "/* {$file->name} */\n" . file_get_contents($folder.$file->static['path']);
				}
			
				break;
			
			
			// default
			default:
				$file = implode("/", $path);
				$content[] = file_get_contents($folder."/".$file);							
				$ext = array_pop(explode(".", $file));
				switch($ext) {
					case 'js': $w::TYPE_JS; break;
					case 'css': $w::TYPE_CSS; break;
					case 'png': $w::TYPE_PNG; break;
					case 'jpg': $w::TYPE_JPG; break;
					case 'gif': $w::TYPE_GIF; break;
				};
		
		};

		// header
		header("Content-Type:". $w::$TYPES[$mime]['mime']);
		
		// print content
		exit(implode("\n\n", $content));
		
	}

}

// look for a wServer env
if (getenv('wServer')) {
	Warhol_Server::run(getenv('wServer'));
}


?>