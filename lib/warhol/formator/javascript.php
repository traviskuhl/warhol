<?php

namespace warhol\formator;
use \warhol;

warhol::formator('\warhol\formator\javascript', array('js'));

class javascript extends \warhol\formator {

	public function getRollups($content) {

		// check 
		if (stripos($content, '@warhol:rollup') === false) {
			return array();
		}

		// rollups
		$rollups = array();

		// get it 
		if (preg_match_all('#warhol:rollup ([^\s]+)#i', $content, $matches)) {
			foreach ($matches[1] as $match) {
				foreach (explode(',', $match) as $rollup) {
					if (stripos($rollup, ':')===false) { $rollup .= ':'; }
					list($name, $weight) = explode(':', $rollup);
					$rollups[$name] = (int)$weight;
				}
			}
		}

		// give them back
		return $rollups;

	}
	
}