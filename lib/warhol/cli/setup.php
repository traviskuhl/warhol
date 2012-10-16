<?php

namespace warhol\cli;
use \warhol;

class setup extends cmd {

    public static $opts = array(
            ""
        );

    public function process() {

    	// commands
    	$username = $this->getCommand(0);
    	$key = $this->getCommand(1, warhol::getUser('home')."/.ssh/id_rsa.pub");

    	// make sure we have 
    	// at least one command
    	if (!$username) {
    		return $this->end("No username provided");
    	}

    	// if no key is provied get from home
    	if ($this->getCommand(1) == false) {
    		// ask them 
    		$key = $this->ask("Public Key", $key, false);
    	}

    	// pass to run
    	$this->run($username, $key);

    }

    public function run($username, $key) {
    	$_key = realpath($key);

    	// key doesn't exist
    	if (!file_exists($_key)) {
    		return $this->end("Public key '$key' does not exist");
    	}

    	// api
    	$url = "http://dev.warholcdn.com/api/v1/user";

    	// try it 
    	list($body, $info) = warhol::curl($url, array(
    			'username' => $username,
    			'key' => file_get_contents($_key)
    		), "POST");

    	// nope
    	if ($info['http_code'] != 200) {
    		return $this->end($body['response']['error']);
    	}

    	// all good, save name and key loc to config
    	$config = new warhol\db(warhol::getUser('home')."/.warholconfig");	

    	// write
    	$config
    		->set('username', $username)
    		->set('key', $key);

    	// done
    	return $this->end("Account with username '$username' created!");

    }

}