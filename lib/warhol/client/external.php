<?php

namespace warhol\client;
use \warhol;

class external extends asset {

	public function init($path) {
		$this->path = $path;
	}

	public function http() {
		return $this->tokenize(rtrim($this->cfg['url']['http'],'/')."/".ltrim($this->path,'/'));
	}

	// build name
	public function getBuildName() {
		return $this->path;
	}

	// buildpath
	public function getBuildPath() {
		return $this->path;
	}	

	// content
	public function getContent() {

		$content = file_get_contents($this->root.$this->file['rel']);

		return $content;
	}

}