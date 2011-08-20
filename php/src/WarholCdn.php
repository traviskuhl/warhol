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
	
	// vars
	private $key, $secret, $oauth, $cache = false;
	private $host = "warholcdn.com";

	///
	/// @brief 
	///	
	public function __construct($config) {
		
		// api key and secret
		foreach($config as $key => $val) {
			if (property_exists($this, $key)) {
				$this->$key = $val;
			}
		}
		
		// oauth consumer
		$this->oauth = new OAuth($this->key, $this->secret, OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION);		
	
	}
	
	
	///
	/// @brief __call
	///
	public function __call($name, $args) {
	
		// do we have a manifest
		if (isset($this->_manifest)) {
			return call_user_func_array(array($this->_manifest, $name), $args);
		}
	
		// go get our latest build
		$build = $this->request("build/latest");
		
			// check for cache settings
	
		// now build our manifest
		$this->_manifest = new Warhol_Manifest($build['response']['build']['manifest']);
		
		// make our call
		return call_user_func_array(array($this->_manifest, $name), $args);
	
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
		if (isset($args['folder'])) {
			$manifest = file_get_contents("{$args['folder']}/.warhol/manifest");
		}
	
		// else stop
		if (!$manifest) {
			throw new Exception("No manifest given"); return;
		}
		
		// parse it 
		return new Warhol_Manifest(json_decode($manifest, true));

	
	}
	
	///
	/// @brief server
	/// 
	public static function server($folder) {

		// get the manifest from the folder
		$manifest = self::parse(array('folder'=>$folder));
		
		// paht info
		$path = explode("/", trim((isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : false),'/'));
		$query = explode('&', (isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ""));

		// content
		$content = array();
		$mime = false;

		// what's in the path
		switch($path[0]) {
		
			// combo
			case 'combo':
								
				// loop through and get them
				foreach ($query as $item) {
					if (strpos($item,'=') !== false) { continue; }										
					$content[] = "/* $item */\n" . file_get_contents($folder . "/" . trim($item,"/"));					
					$mime = (strpos($item,".css")!==false ? self::TYPE_CSS : self::TYPE_JS);
				}
			
				// break
				break;
			
			// rollup
			case 'rollup':
			
				// rollups
				$rollups = $manifest->rollups;
					
					// no rollups
					if (!$rollups) { die; }
					
				// name
				$name = $path[1];		
				
					
				// rollup
				$rollup = array();	
							
				// find the rollup in the manifest
				if (isset($rollups['style']) AND array_key_exists($name, $rollups['style'])) {
					$rollup = $rollups['style'][$name];
					$mime = self::TYPE_CSS;
				}

				if (isset($rollups['javascript']) AND  array_key_exists($name, $rollups['javascript'])) {
					$rollup = $rollups['javascript'][$name];
					$mime = self::TYPE_JS;
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
				$mime = (strpos($file,".css")!==false ? self::TYPE_CSS : self::TYPE_JS);				
		
		};

		// header
		header("Content-Type:{$mime}");
		
		// print content
		exit(implode("\n\n", $content));
		
	}

	///
	/// @brief
	///
	public static function getFileId($path) {
		return md5("/".trim($path,'/'));
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
	private $_files = array();

	public function __construct($manifest) {
	
		// globalize manifest
		$this->_manifest = $manifest;
		
		// globalize some config stuff
		$this->_http = $manifest['config']['root']['http'];
		$this->_https = $manifest['config']['root']['https'];
		
		// lets loop through and 
		foreach ($manifest['files'] as $file) {		
			$this->_files[$file['id']] = new Warhol_Manifest_Item($file, $this);		
			switch($file['type']['id']) {
			
				// css
				case Warhol::TYPE_CSS:
					$this->_style[$file['id']] = $this->_files[$file['id']]; break; 
					
				// js
				case Warhol::TYPE_JS:
					$this->_javascript[$file['id']] = $this->_files[$file['id']]; break;
					
				
				// image
				case Warhol::TYPE_PNG:
				case Warhol::TYPE_JPG:
				case Warhol::TYPE_GIF:
					$this->_images[$file['id']] = $this->_files[$file['id']]; break;
					
				// unknow
				default:
					continue;					
			};
		}
	
	}
	
	public function getFile($fid) {
	
		return (array_key_exists($fid, $this->_files) ? $this->_files[$fid] : false );
	}
	
	public function __get($name) {
		return (array_key_exists($name, $this->_manifest) ? $this->_manifest[$name] : false );
	}
	
	public function getManifest(){ 
		return $this->_manifest;
	}
	
	public function getStyle() {	
		return $this->_style;
	}
	
	public function getJavascript() {
		return $this->_javascript;
	}
	
	public function getImages() {
		return $this->_images;
	}
	
	public function getComboUrl($type=false, $files=false, $ssl=false) {
		$items = ($type == TYPE_JS ? $this->_javascript : $this->_style );
		return $this->getUrl(array(
			'root' => "combo",
			'paths' => ($files ? $files : array_map(function($item){ return $item['path']; }, $items))
		), $ssl);		
	}

	public function getRollupUrl($name, $type=false, $ssl=false) {
		$items = ($type == TYPE_JS ? $this->_javascript : $this->_style );
		return $this->getUrl("rollup/{$name}", $ssl);		
	}
	
	public function getImageUrl($path, $cmds=array(), $ssl=false) {
		return $this->getUrl("image/{$path}?".implode("&", array_map(function($k,$v){ return "{$k}=".urlencode($v); }, array_keys($cmds), $cmds)));
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
	
	public function getImageUrl($cmds=array(), $ssl) {
		return $this->_manifest->getImageUrl($this->path, $cmds, $ssl);
	}

}

// look for a wServer env
if (getenv('wServer')) {
	Warhol::server(getenv('wServer'));
}


?>