<?php

namespace warhol\formator;
use \warhol;

// style shee
warhol::formator('fonts', '\warhol\formator\fonts', array('eot','svg','tff','woff'));

class fonts extends \warhol\formator {

	// simple passthrough
	public function format($body, $file) {
		return $body;
	}
	
}