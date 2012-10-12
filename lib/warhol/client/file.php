<?php

namespace warhol\client;
use \warhol;

class file extends asset {
	
	public function init($id) {
		$this->_id = $id;		

		// loaded
		$this->_loaded = $this->manifest()->get('files')->exists($this->_id);

		// file
		$this->file = $this->manifest()->get('files')->get($this->_id);

		// get the right formator for this file
		$this->formator = warhol::getFormator($this->file['ext']);

	}

	// content
	public function getContent() {
		$content = file_get_contents($this->root.$this->file['rel']);
	}

	// mime
	public function getMimeType() {
		return $this->formator->mime;
	}

}