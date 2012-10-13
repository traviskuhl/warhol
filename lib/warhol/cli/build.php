<?php

namespace warhol\cli;

// need tar
require "Archive/Tar.php";

// localize
use \warhol;
use \Archive_Tar;

class build extends cmd {

	public static $opts = array(
			""
		);

	public function run($match='.', $opts=array()) {
		$root = false; 

		// get our root
		$this->getConfig($root);

		// files
		$files = $this->manifest->get('files')->all();

			// no files
			if (count($files) === 0) {
				return $this->end("No files in manifest. try running ./warhol add .");
			}

		// build id
		$bid = time();

		// build name
		$build = "{$this->config->name}-{$bid}.tar.gz";
	
		// create a new tar		
		$update = array();

		// perfect, lets loop through the files
		foreach ($files as $file) {

			// file path
			$path = $this->config->root.$file['rel'];

			// see if things have changed for this file
			$f = $this->finfo($path);

			// hasn't changed and has been built
			if ($file['bid'] == $bid AND $f['mtime'] == $file['mtime'] AND $f['md5'] == $file['md5']) {
				continue;
			}

			// update the bid
			$f['bid'] = $bid;

			// update in the manifest
			$this->manifest->set($f['id'], $f, 'files');

			// files updated
			$update[] = $f['id'];

		}

		// no updates
		if (count($update) === 0) {
			return $this->end("Everything is up-to-date");
		}

		// tmp
		$tmp = "/tmp/w".time(); mkdir($tmp);
		$cwd = getcwd();

		// tar
		$tar = new Archive_Tar("{$cwd}/{$build}", 'gz');

		// move in
		chdir($tmp);

		// loop through each updated file
		foreach ($update as $fid) {
			$formators = array();

			// get hte file
			$file = $this->manifest->get('files')->get($fid);

			// file path
			$path = $this->config->root.$file['rel'];

			// new file, lets loop through our formators 
			// and figure out what we need to run
			// if they don't give a list in settings
			// we run them based on ext
			if (isset($file['settings']['format'])) {
				$formators = warhol::getFormators('name', $file['settings']['format']);
			}
			else {
				$formators = warhol::getFormators('ext', $file['ext']);
			}

			// content
			$content = file_get_contents($path);

			// loop through each format
			foreach ($formators as $o) {
				$o->setManifest($this->manifest);
				$o->setConfig($this->config);
				$resp = $o->format($content, $file);
				if ($resp) {
					$content = $resp;
				}
			}

			// dir
			$tdir = "{$tmp}{$file['dir']}";
			$tfile = ".{$file['dir']}{$file['name']}-{$file['bid']}.{$file['ext']}";

			// write to the tmp dir
			system("mkdir -p $tdir");

			// write
			file_put_contents($tfile, $content);

			// add it tar
			$update[] = $tfile;

		}

		// create the tar
		$tar->create($update);

		// move back
		chdir($cwd);

		// remove tmp
		`rm -r $tmp`;

		// tar
		return $this->end("Build created");

	}

}