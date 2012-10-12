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
		$config = $this->getConfig($root);

		var_dump($config); die;


	}

}