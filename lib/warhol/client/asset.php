<?php

namespace warhol\client;
use \warhol;

class asset extends \warhol\plugin {
	protected $_loaded = false;

	public function loaded() {
		return $this->_loaded;
	}

}