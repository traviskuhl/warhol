<?php

namespace warhol;
use \warhol;

class cli extends plugin {

	////////////////////////////////////////////
	/// @brief initiate the clie class. called by
	///			plugin::__construct
	///
	/// @return void
	////////////////////////////////////////////
	public function run(){

		// args
		$args = (is_array($GLOBALS['argv']) ? $GLOBALS['argv'] : array());

		// don't need the script name
		$script = array_shift($args);

		// default command is help
		$cmds = array(); $opts = array();

		$f = false;

		// loop through our arguments and
		// find our first command
		foreach ($args as $i => $arg) {
			if ($arg{0} != '-' AND $f === false) {
				$cmds[] = $arg;
			}
			else if (substr($arg,0,2) == '--') {
				if (stripos($arg, '=') !== false) {
					list($k,$v) = explode("=", $arg);
					$opts[ltrim($k, '-')] = $v;
				}
				else {
					$opts[ltrim($arg, '-')] = true;
				}
			}
			else if ($f) {
				$f = false;
			}
			else {
				if (isset($args[$i+1]) AND $args[$i+1]{0} != '-') {
					$opts[ltrim($arg,'-')] = $args[$i+1];
					$f = true;
				}
				else {
					$opts[ltrim($arg,'-')] = true;
					$f = false;
				}
			}
		}

		// our first cmd is
		// the module to execute
		$cmd = array_shift($cmds);

		// class name of command
		$class = '\\warhol\\cli\\'.$cmd;

		// see if we have a command
		if (!class_exists($class, true)) {
			$this->end("No command '{$cmd}'");
		}

		// we have that command
		// create our class
		$c = new $class($this);

		// set our commands
		$c->setCommands($cmds)->setOptions($opts)->process();

	}

	public function end($msg) {
		$this->out($msg);
		exit;
	}

	public function out() {
		$args = func_get_args();
		if (count($args) == 1) {
			echo $args[0]."\n";
		}
		else if (count($args) == 2) {
			echo sprintf($args[0], $args[1]);
		}
		else {
			foreach ($args as $line) {
				call_user_func_array(array($this, 'out'), $line);
			}
		}
	}

}