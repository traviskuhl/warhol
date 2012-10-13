<?php

namespace warhol\formator;
use \warhol;

// style shee
warhol::formator('css-images', '\warhol\formator\cssImages', array('css'));

class cssImages extends \warhol\formator {

	// format
	public function format($body, $file) {

		// config room
		$croot = $this->getConfig()->root;

		// figure out the root of this file
		$root = dirname(realpath($croot.$file['rel']));

		// find all images
		if (preg_match_all("#url\(([^\)]+)\)#", $body, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$val = trim($match[1],"'\"/");
				$fpath = str_replace($croot, '', realpath($root."/".$val));				
				// see if we have this file
				$f = $this->getManifest()->get('files')->get(md5($fpath));
				if ($f) {
					$body = str_replace($match[0], str_replace("{$f['name']}.{$f['ext']}", "{$f['name']}-{$f['bid']}.{$f['ext']}", $match[0]), $body);
				}
			}
		}

		// body
		return $body;

	}
	
}