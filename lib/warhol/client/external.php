<?php

namespace warhol\client;
use \warhol;

class external extends asset {

	public function init($path) {
		$this->path = $path;
	}

	public function http() {
		return rtrim($this->cfg['url']['http'],'/')."/".ltrim($this->path,'/');
	}

	// content
	public function getContent() {

		$content = file_get_contents($this->root.$this->file['rel']);

		return $content;
	}

}