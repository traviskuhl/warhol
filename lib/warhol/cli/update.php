<?php

namespace warhol\cli;
use \warhol;

class update extends cmd {

	public static $opts = array(
			""
		);

	public function process() {
			
		// root
		$this->run($this->getCommand(0));

	}

	public function run($root=false) {

		// get our root
		$this->getConfig($root);

		// read the files
		foreach ($this->manifest->get('files')->all()  as $file) {
			$path = $this->config->root.$file['rel'];
			if (is_file($path)) {
				$f = $this->finfo($path);
				$this->manifest->set($f['id'], $f, 'files');
			}	
		}

		// done
		return $this->end("Project folder updated.");

	}

}