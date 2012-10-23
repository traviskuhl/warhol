<?php

namespace warhol\formator;
use \warhol;

// style shee
warhol::formator('css-less', '\warhol\formator\cssLess', array('css'));

class cssLess extends \warhol\formator {

	// format
	public function format($body, $file) {

		// config room
		$croot = $this->getConfig()->root;

		// figure out the root of this file
		$src = realpath($croot.$file['rel']);

		$descriptorspec = array(
		   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
		);

		$process = proc_open('lessc -', $descriptorspec, $pipes, dirname($src), array());

		if (is_resource($process)) {

		    fwrite($pipes[0], $body);
		    fclose($pipes[0]);

		    $body = stream_get_contents($pipes[1]);

		    fclose($pipes[1]);

		}

		// body
		return $body;

	}

}