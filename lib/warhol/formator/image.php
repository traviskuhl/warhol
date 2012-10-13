<?php

namespace warhol\formator;
use \warhol;

warhol::formator('image', '\warhol\formator\image', array(
		'jpg','png','gif','jpeg','ico'
	));

class image extends \warhol\formator {

	public function format($body, $file) {

	}
	
}