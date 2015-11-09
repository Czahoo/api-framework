<?php
namespace Api\Framework\Communication\Object;

use Api\Framework\Errors\ObjectErrors;
use Api\Framework\Utility\Helper\Formatter;
use Api\Framework\Utility\Helper\ArrayHelper;
use Api\Framework\Errors\Basic\ErrorGlobal;
use Api\Framework\Errors\Basic\ErrorForm;
use Api\Framework\Errors\Error;
use Api\Framework\Errors\ErrorPrototype;

class Message implements \JsonSerializable
{
    use ObjectErrors;

    const KEY_DATA = "data";

    const KEY_SUCCESS = "success";

    const KEY_ERRORS = "errors";

    /**
     * Storage for message data
     * @var array
     */
    protected $data;
    /**
     * Flag if operation was a success
     * @var bool
     */
    protected $success;

    public function __construct($data = [])
    {
        $this->initErrors();
        $this->initFromData($data);
    }

    /**
     * If $data is null then return $value else return unchanged $data
     * @author Krzysztof Kalkhoff
     *
     * @param mixed $data
     * @param mixed $value
     * @return mixed
     */
    protected function defaultValue($data, $value)
    {
        return is_null($data) ? $value : $data;
    }

    /**
     * Used to parse array of data obtained from json to init object 
     * @author Krzysztof Kalkhoff
     *
     * @param array $data
     * @return Message
     */
    public function initFromData($data)
    {
        $this->setData($this->defaultValue((!isset($data[self::KEY_ERRORS]) && !isset($data[self::KEY_SUCCESS]) ? $data : $data[self::KEY_DATA]), []))
            ->setErrors($this->defaultValue($data[self::KEY_ERRORS], []))
            ->setSuccess($this->defaultValue($data[self::KEY_SUCCESS], ! $this->hasErrors()));
        return $this;
    }

    /**
     * Set internal $data variable<br>
     * <strong>Warning</strong>: data property contains all information about errors, message data and success, so this function will override it 
     * @author Krzysztof Kalkhoff
     *
     * @param array $data
     * @return Message
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set success flag
     * @author Krzysztof Kalkhoff
     *
     * @param bool $success
     * @return Message
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * Set errors from array obtained through getErrorsList on Error object
     * @author Krzysztof Kalkhoff
     *
     * @param array $errors
     * @return Message
     */
    public function setErrors($errors)
    {
        foreach ($errors as $error) {
            if ($error[ErrorGlobal::ERROR_ARRAY_TYPE] == ErrorGlobal::TYPE) {
                $this->addError($error[ErrorGlobal::ERROR_ARRAY_MSG]);
            } elseif($error[ErrorForm::ERROR_ARRAY_TYPE] == ErrorForm::TYPE) {
                $this->addError($error[ErrorForm::ERROR_ARRAY_FIELD], $error[ErrorForm::ERROR_ARRAY_MSG]);
            }
        }
        return $this;
    }
    
    /**
     * Get array representation of errors (merge global with forms)
     * @author Krzysztof Kalkhoff
     *
     * @return array
     */
    public function getErrorsList() {
        return array_merge($this->errors->getErrorsList(ErrorGlobal::TYPE), $this->errors->getErrorsList(ErrorForm::TYPE));
    }

    /**
     * (non-PHPdoc)
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        $return = [
            self::KEY_DATA => $this->data,
            self::KEY_SUCCESS => $this->success,
            self::KEY_ERRORS => $this->getErrorsList()
        ];
        return $return;
    }

    /**
     * Return value of success flag
     * @author Krzysztof Kalkhoff
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->success;
    }

    /**
     * If data of message is empty
     * @author Krzysztof Kalkhoff
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Get $data property
     * @author Krzysztof Kalkhoff
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}