<?php

namespace warhol\formator;
use \warhol;

// style shee
warhol::formator('flash', '\warhol\formator\flash', array('swf'));

class flash extends \warhol\formator {

    // simple passthrough
    public function format($body, $file) {
        return $body;
    }
    
}