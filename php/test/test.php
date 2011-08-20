#!/usr/bin/php
<?php

// require
require("../src/WarholCdn.php");

/// key and secret
$key = "6OIRI2NVdvCKJttHUvyzURNDucZk5CyOT1V1Vj5IUbAoY";
$secret = "1313344569-4e480c39210edwBJ9ettVF4Yqjvu";

// parse
$warhol = Warhol::parse(array('folder' => "../../cli/test/"));

// print out each stylesheet
foreach($warhol->style() as $item) {
	var_dump($item->getUrl());
}

echo "\n\n===================\n\n";

// get from server
$w = new Warhol(array('key' => $key, 'secret' => $secret, 'host' => "dev.warholcdn.com"));

// print out each stylesheet
foreach($w->style() as $item) {
	var_dump($item->getUrl());
}

?>