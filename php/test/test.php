#!/usr/bin/php
<?php

require("../src/WarholCdn.php");

// parse
$warhol = Warhol::parse(array('dir' => "../../cli/test/"));

// print out each stylesheet
foreach($warhol->style() as $item) {

	var_dump($item->getUrl()); die;

}

?>