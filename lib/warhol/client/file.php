<?php

namespace warhol\client;
use \warhol;

class file extends asset implements \ArrayAccess {

	public function __get($name) {
		return (array_key_exists($name, $this->file) ? $this->file[$name] : false);
	} 

	public function init($id) {
		$this->_id = $id;

		// loaded
		$this->_loaded = $this->manifest()->get('files')->exists($this->_id);

		// file
		$this->file = $this->manifest()->get('files')->get($this->_id);

		// get the right formator for this file
		$this->formator = warhol::getFormator($this->file['ext']);

	}

	public function http() {
		return rtrim($this->cfg['url']['local'],'/').$this->file['rel'];
	}

	// content
	public function getContent() {

		$content = file_get_contents($this->base->root.$this->file['rel']);

		return $content;
	}

	// format
	public function format() {		

		// new file, lets loop through our formators 
		// and figure out what we need to run
		// if they don't give a list in settings
		// we run them based on ext
		if (isset($this->file['settings']['format'])) {
			$formators = warhol::getFormators('name', $this->file['settings']['format']);
		}
		else {
			$formators = warhol::getFormators('ext', $this->file['ext']);
		}


		// content
		$content = $this->getContent();

		// loop through each format
		foreach ($formators as $o) {
			$o->setManifest($this->manifest());
			$o->setConfig($this->config());
			$resp = $o->format($content, $this->file);
			if ($resp) {
				$content = $resp;
			}
		}

		return $content;

	}

	// build name
	public function getBuildName() {
		return $this->name . '-' . $this->getBid() . '.' . $this->ext;
	}

	// buildpath
	public function getBuildPath() {
		return $this->dir . $this->getBuildName();
	}

	// mime
	public function getMimeType() {
		return $this->formator->mime;
	}

   public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->file[] = $value;
        } else {
            $this->file[$offset] = $value;
        }
    }
    public function offsetExists($offset) {
        return isset($this->file[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->file[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->file[$offset]) ? $this->file[$offset] : null;
    }

} 