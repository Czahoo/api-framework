<?php
namespace Api\Framework\Basic\Traits;

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