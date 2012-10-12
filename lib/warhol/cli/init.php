<?php

namespace warhol\cli;
use \warhol;

class init extends cmd {

	public static $opts = array(

		);

	// process command line arguments
	public function process() {

		// make sure we have at least a name
		if (count($this->commands) == 0) {
			$this->end("No project name given");
		}

		// call run
		return $this->run($this->commands[0]);

	}

	////////////////////////////////////////////
	/// @brief run the init command
	///
	/// @param $name name of new project
	/// @return void
	////////////////////////////////////////////
	public function run($name) {
		
		// where are we 
		$folder = realpath(getcwd());
		$root = $folder."/.warhol";

		// first lets see if 
		// there's a project already
		// inited in this location
		if (file_exists($root)) {
			$this->end("Project folder already exists at: '$root'");
		}

		// nope, lets start one
		mkdir($root);

			// no folder still
			if(!file_exists($root)) {
				$this->end("Unable to write root folder to: '$root'");
			}

		// set some data
		$config = new warhol\db("$root/config");

		// config
		$config->set(array(
				'root' => $folder,
				'name' => $name,
				'_' => array(
						'created' => time(),
						'by' => warhol::getUser()
					)
			));

		// all good
		$this->end("Complete!");

	}

}