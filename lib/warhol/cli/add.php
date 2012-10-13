<?php

namespace warhol\cli;
use \warhol;

class add extends cmd {

	public static $opts = array(
			""
		);

	public function run($match='.', $opts=array()) {
		$root = false; 

		// figure out if match is a glob or a folder
		if (file_exists($match)) {	
			$root = $match = realpath($match);
		}
		else if (stripos($match, '/') === false) { 
			$root = realpath(getcwd());
		}
		else if (stripos($match, '*') !== false) {
			// we need to get our path			
			$path = explode("/", $match);
			array_pop($path);
			$root = realpath(($match{0}=='/' ? '/' : '').implode("/", $path));
		}

		// get our root
		$this->getConfig($root);

		// files
		$files = array();

		// types we can hanel
		$types = warhol::getFromatorExt();

		// sweet, no figure out if match is a file
		if (is_file($match)) {
			$files[] = realpath($match);
		}	
		else if (stripos($match, '*') !== false) {
			$files = glob($match);
		}
		else if (is_dir($match)) {

			// recursivly just through the directory they gave			
			$dir = new \RecursiveDirectoryIterator($match);
			$it = new \RecursiveIteratorIterator($dir);
			$regex = new \RegexIterator($it, '/^.+\.('.implode('|',$types).')$/i', \RecursiveRegexIterator::GET_MATCH);

			// add our files
			foreach (iterator_to_array($regex) as $file) {
				if (is_file($file[0])) {
					$files[] = $file[0];
				}
			}

		}

		// loop through each file and figure out what 
		// type it is and if we have an oporator
		foreach ($files as $file) {		

			// f
			$f = $this->finfo($file);			

			// ok lets add these files to the manifest
			$this->manifest->set($f['id'], $f, 'files');
		
		}

		// tell them 
		$this->end("Files added from '$match'");

	}

}