#!/usr/bin/php
<?php

// require
require("../src/WarholCdn.php");

/// key and secret
$key = "6OIRI2NVdvCKJttHUvyzURNDucZk5CyOT1V1Vj5IUbAoY";
$secret = "1313344569-4e480c39210edwBJ9ettVF4Yqjvu";

// parse
$warhol = new WarholCdn(array(
	'folder' => "../../cli/test/",
	'ssl' => true,
	'local' => "/"
));

echo "\n\n===================\n\n";

echo $warhol->comboUrl('style');

echo "\n\n===================\n\n";

echo $warhol->getComboUrl('style')->host('http');

echo "\n\n===================\n\n";

echo $warhol->getComboUrl('style')->host('test.com');

echo "\n\n===================\n\n";

echo $warhol->getComboUrl('style')->host('test.com')->scheme('http');

echo "\n\n===================\n\n";


// get from server
$w = new WarholCdn(array('key' => $key, 'secret' => $secret, 'host' => "dev.warholcdn.com"));

echo "\n\n===================\n\n";

echo $warhol->comboUrl('style');

echo "\n\n===================\n\n";

echo $warhol->getComboUrl('style')->host('http');

echo "\n\n===================\n\n";

echo $warhol->getComboUrl('style')->host('test.com');

echo "\n\n===================\n\n";

echo $warhol->getComboUrl('style')->host('test.com')->scheme('http');

?>