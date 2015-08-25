<?php
namespace Api\Framework\Errors;

use Api\Framework\Errors\Basic\ErrorGlobal;
use Api\Framework\Errors\Basic\ErrorForm;

trait ObjectErrors
{

    protected $errors = null;

    protected function initErrors()
    {
        $this->errors = new Error();
    }

    public function addError()
    {
        if (func_num_args() < 1) {
            \Framework::debug("Need at least 1 parameter");
        }
        
        $args = func_get_args();
        switch (count($args)) {
            case 1:
                if (is_object($args[0])) {
                    $this->errors->add($args[0]);
                } elseif (is_string($args[0])) {
                    $this->errors->add(new ErrorGlobal($args[0]));
                } else {
                    \Framework::debug("Wrong argument passed to add errors");
                }
                break;
            case 2:
                $this->errors->add(new ErrorForm($args[0], $args[1]));
                break;
            
            case 3:
                $this->errors->add(new ErrorForm($args[0], $args[1], $args[2]));
                break;
            
            default:
                \Framework::debug("This function can't has " . count($args) . " arguments");
                break;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return $this->errors->hasErrors();
    }
}