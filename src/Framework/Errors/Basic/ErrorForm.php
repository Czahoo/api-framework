<?php
namespace Api\Framework\Errors\Basic;

use Api\Framework\Errors\ErrorPrototype;
use Api\Framework\Errors\Error;
use Api\Framework\Errors\ErrorInterface;

/**
 * Handles form errors
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class ErrorForm extends ErrorPrototype implements ErrorInterface
{

    const TYPE = "FORM_ERROR";

    protected $rowNr;

    public function __construct($name, $msg, $rowNr = 0)
    {
        $this->fieldName = $name;
        $this->message = $msg;
        $this->rowNr = $rowNr;
    }

    public function getRowNr()
    {
        return $this->rowNr;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    public function getType()
    {
        return self::TYPE;
    }

    public function getHTML()
    {
        return '<div class="form-error"><p>' . $this->message . '</p></div>';
    }
}