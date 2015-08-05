<?php
namespace Api\Framework\Errors\Basic;

use Api\Framework\Errors\ErrorPrototype;
use Api\Framework\Errors\Error;
use Api\Framework\Errors\ErrorInterface;

/**
 * Handles global errors
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class ErrorGlobal extends ErrorPrototype implements ErrorInterface
{

    const TYPE = "GLOBAL_ERROR";

    public function getType()
    {
        return Error::TYPE_GLOBAL;
    }

    public function getHTML()
    {
        return '<div class="error"><p>' . $this->message . '</p></div>';
    }
}