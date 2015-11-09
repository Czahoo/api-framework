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

    protected $data, $success;

    public function __construct($data = [])
    {
        $this->initErrors();
        $this->initFromData($data);
    }

    protected function defaultValue($data, $value)
    {
        return is_null($data) ? $value : $data;
    }

    public function initFromData($data)
    {
        $this->setData($this->defaultValue((!isset($data[self::KEY_ERRORS]) && !isset($data[self::KEY_SUCCESS]) ? $data : $data[self::KEY_DATA]), []))
            ->setErrors($this->defaultValue($data[self::KEY_ERRORS], []))
            ->setSuccess($this->defaultValue($data[self::KEY_SUCCESS], ! $this->hasErrors()));
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setSuccess($bool)
    {
        $this->success = $bool;
        return $this;
    }

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
    
    public function getErrorsList() {
        return array_merge($this->errors->getErrorsList(ErrorGlobal::TYPE), $this->errors->getErrorsList(ErrorForm::TYPE));
    }

    public function jsonSerialize()
    {
        $return = [
            self::KEY_DATA => $this->data,
            self::KEY_SUCCESS => $this->success,
            self::KEY_ERRORS => $this->getErrorsList()
        ];
        return $return;
    }

    public function isValid()
    {
        return $this->success;
    }

    public function isEmpty()
    {
        return empty($this->data);
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Translates data from message using several conditions
     * @author Krzysztof Kalkhoff
     *
     * @param array $dictionary Array containing mapping from old key to new key
     * @param string $removeOldKey If after successful mapping old key should be removed from returned array
     * @param bool|array $initWithData Can be bool to set that we init data with $this->getData() function or array to init directectly with passed data
     * @return array
     */
    public function translateData($dictionary, $removeOldKey = true, $initWithData = true)
    {
        $data = $this->getData();
        $return = is_bool($initWithData) ? ($initWithData ? $data : []) : (is_array($initWithData) ? $initWithData : []);
        foreach ($dictionary as $oldKey => $newKey) {
            if (isset($data[$oldKey])) {
                $return[$newKey] = $data[$oldKey];
                if($removeOldKey && isset($return[$oldKey])) {
                    unset($return[$oldKey]);
                }
            }
        }
        return $return;
    }
}