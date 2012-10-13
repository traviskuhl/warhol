<?php

namespace warhol;
use \warhol;

class server extends plugin {

	// cfg
	protected $cfg = array();

	public function init($cfg) {
		$this->cfg = $cfg;

		// root
		$root = realpath(isset($cfg['root']) ? $cfg['root'] : ".");

		// lets get our manifest
		$this->client = warhol::client(array(
				'manifest' => "$root/.warhol/manifest",
				'root' => "$root/"
			));

			// no manifest we cry
			if (!$this->client->loaded()) {
				exit("Unable to load manifest");
			}

		// get the path
		$path = explode('/', trim($_SERVER['REQUEST_URI'], '/')); array_shift($path);

		// figure out if there's something
		// specical we need to do
		if ($path[0] == 'rollup') {
			$this->rollup($path[1]);
		}
		else if ($path[0] == 'combo') {

		}
		else {
			$this->file("/".implode("/", $path));
		}


	}

	// yes
	public function file($path) {

		// fid
		$file = $this->client->file('path', $path);

		// see if it's in our manifest
		if (!$file->loaded()) {
			exit("Unable to load file '$path'");
		}

		// print
		$this->_print($file);

	}

	// rollup
	public function rollup($name) {
		// get stuff
		list($name, $ext) = explode('.', $name);

		// get it 
		$rollup = $this->client->rollup($name);

		// rollup
		$this->_print($rollup);

	}


	// header
	public function _print($asset) {

		// header
		header('Content-Type:' . $asset->getMimeType());

		// contnet
		exit($asset->getContent());

	}

}