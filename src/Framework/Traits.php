<?php
use Api\Framework\Errors\Error;
use Api\Framework\Errors\Basic\ErrorGlobal;
use Api\Framework\Errors\Basic\ErrorForm;
trait Singleton {
	protected static $instance;
	final public static function getInstance()
	{
		return isset(static::$instance) ? static::$instance : (static::$instance = new static);
	}
	final private function __construct() {
		if(method_exists($this, "init")) {
			$this->init();
		}
	}
	protected function init() {}
	final private function __wakeup() {}
	final private function __clone() {}
}

trait AutoGetters {
	public function __get($name) {
		$method = "get".ucfirst($name);
		if(method_exists($this, $method)) {
			return $this->$method();
		} elseif(property_exists($this, $name)) {
			return $this->$name;
		}
	
		// If neither property nor method exists
		error_log("Trying to access unexisting value of '{$name}' by __get()");
		return NULL;
	}
}

trait ObjectErrors {
	protected $errors = null;
	
	protected function initErrors() {
		$this->errors = new Error();
	}
	
	public function addError() {
		if(func_num_args() < 1) {
			debug("Need at least 1 parameter");
		}
	
		$args = func_get_args();
		switch (count($args)) {
			case 1:
				if(is_object($args[0])) {
					$this->errors->add($args[0]);
				} elseif(is_string($args[0])) {
					$this->errors->add(new ErrorGlobal($args[0]));
				} else {
					debug("Wrong argument passed to add errors");
				}
				break;
			case 2:
				$this->errors->add(new ErrorForm($args[0], $args[1]));
				break;
	
			case 3:
				$this->errors->add(new ErrorForm($args[0], $args[1], $args[2]));
				break;
	
			default:
				debug("This function can't has ".count($args)." arguments");
				break;
		}
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function hasErrors() {
		return $this->errors->hasErrors();
	}
}