<?php
namespace Api\Framework\Errors;

use Api\Framework\Errors\ErrorPrototype;

/**
 * Basic class for error handling
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class Error
{

    const LOG_FILE_DIR = "errors.log";

    const ERROR_MESSAGE_RAW = "msg_raw";

    const ERROR_MESSAGE_HTML = "msg_html";

    /**
     * Storage for errors
     *
     * @var array
     */
    protected $list = array();

    /**
     * Add error to list
     *
     * @author Krzysztof Kalkhoff
     *        
     * @param ErrorPrototype $errorObj            
     * @return \Api\Framework\Errors\Error
     */
    public function add(ErrorPrototype $errorObj)
    {
        $type = $errorObj->getType();
        if (! array_key_exists($type, $this->list)) {
            $this->list[$type] = array();
        }
        $this->list[$type][] = $errorObj;
        return $this;
    }

    /**
     * Quick create object, add error and return
     *
     * @author Krzysztof Kalkhoff
     *        
     * @param ErrorPrototype $errorObj            
     * @return \Api\Framework\Errors\Error
     */
    public static function addAndReturn(ErrorPrototype $errorObj)
    {
        $error = new self();
        return $error->add($errorObj);
    }

    /**
     * Check if there were any errors added
     *
     * @author Krzysztof Kalkhoff
     *        
     * @return boolean
     */
    public function hasErrors()
    {
        return ! array_empty($this->list);
    }

    /**
     * Get list of errors with given type
     *
     * @author Krzysztof Kalkhoff
     *        
     * @param string $type            
     * @return array
     */
    public function getList($type = NULL)
    {
        return is_null($type) ? $this->list : (array_key_exists($type, $this->list) ? $this->list[$type] : array());
    }

    /**
     * Get list containing string representation of errors
     *
     * @author Krzysztof Kalkhoff
     *        
     * @param string $type            
     * @param string $msgType            
     * @return array
     */
    public function getErorrsList($type = NULL, $msgType = self::ERROR_MESSAGE_RAW)
    {
        $return = array();
        if (is_null($type)) {
            $types = array_keys($this->list);
        } else {
            if (! array_key_exists($type, $this->list)) {
                $this->list[$type] = array();
            }
            $types = array($type);
        }
        
        foreach ($types as $listType) {
            $return[$listType] = array();
            if (! empty($this->list[$listType])) {
                foreach ($this->list[$listType] as $key => $errorObj) {
                    $errorObj->addToList($return[$listType], $msgType);
                }
            }
        }
        
        return is_string($type) ? $return[$type] : $return;
    }
}