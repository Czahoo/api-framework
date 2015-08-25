<?php
namespace Api\Framework\Errors;

use Api\Framework\Errors\Error;

/**
 * Error prototype
 * 
 * @author Krzysztof Kalkhoff
 *        
 */
abstract class ErrorPrototype
{

    const ERROR_ARRAY_TYPE = "error_type";

    const ERROR_ARRAY_FIELD = "error_field";

    const ERROR_ARRAY_MSG = "error_message";

    protected $message, $fieldName = NULL;

    public function __construct($msg)
    {
        $this->message = $msg;
    }

    public function getRawMessage()
    {
        return $this->message;
    }

    public function getHTML()
    {
        return $this->message;
    }

    public function hasFieldName()
    {
        return ! is_null($this->fieldName);
    }

    public function setFieldName($name)
    {
        $this->fieldName = $name;
        return $this;
    }

    public function getFieldName()
    {
        return $this->fieldName;
    }

    protected function getMessage($msgType)
    {
        switch ($msgType) {
            case Error::ERROR_MESSAGE_HTML:
                return $this->getHTML();
                break;
            
            case Error::ERROR_MESSAGE_RAW:
                return $this->getRawMessage();
                break;
            
            default:
                \Framework::debug("Unknown message type");
                break;
        }
    }

    protected function getErrorArray($msgType)
    {
        $return = array();
        $return[self::ERROR_ARRAY_MSG] = $this->getMessage($msgType);
        $return[self::ERROR_ARRAY_TYPE] = $this->getType();
        if ($this->hasFieldName()) {
            $return[self::ERROR_ARRAY_FIELD] = $this->getFieldName();
        }
        return $return;
    }

    public function addToList(&$list, $msgType = Error::ERROR_MESSAGE_RAW)
    {
        $list[] = $this->getErrorArray($msgType);
    }
}