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
        return self::TYPE;
    }

    public function getHTML()
    {
        return '<div class="error error-global"><p>' . $this->message . '</p></div>';
    }
}