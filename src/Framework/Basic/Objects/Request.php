<?php
namespace Api\Framework\Basic\Objects;

/**
 * Basic request object handling GET and POST data, used in controllers
 * 
 * @author Krzysztof Kalkhoff
 * @author Mateusz Kleska
 *        
 */
class Request
{

    const TYPE_GET = "GET";

    const TYPE_POST = "POST";

    const SSL_PORT = 443;

    const HTTP = "http";

    const HTTPS = "https";

    /**
     * TODO Fill this
     * 
     * @var string NULL
     */
    protected $endpoint = NULL;

    /**
     * TODO Fill this
     * 
     * @var array
     */
    protected $params = array(self::TYPE_GET => array(),self::TYPE_POST => array());

    /**
     * TODO Fill this
     * 
     * @var array NULL
     */
    protected $expectedParams = NULL;

    /**
     * If request is valid
     * 
     * @var bool NULL
     */
    protected $valid = NULL;

    /**
     * Create request object using query and request data
     *
     * @author Krzysztof Kalkhoff
     * @author Mateusz Kleska
     *        
     * @param array $query
     *            By default it should be data from $_GET
     * @param array $request
     *            By default it should be data from $_POST
     * @param string $endpoint
     *            TODO Fill this
     */
    public function __construct($query = array(), $request = array(), $endpoint = NULL)
    {
        $this->params[self::TYPE_GET] = $query;
        $this->params[self::TYPE_POST] = $request;
        $this->endpoint = $endpoint;
    }

    /**
     * Create request object using $_GET and $_POST
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return \Api\Framework\Basic\Objects\Request
     */
    public static function createFromGlobals()
    {
        return new self($_GET, $_POST);
    }

    /**
     * Check existance of params inside the object<br>
     * Returns true only if all params exist
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param array $expectedKeys
     *            Array of params keys to check existance
     * @param string $type
     *            Param type (as defined in constants)
     * @return bool
     */
    public function checkParamsExistance($expectedKeys, $type = self::TYPE_POST)
    {
        foreach ($expectedKeys as $key) {
            if (! isset($this->params[$type][$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Trims params to list of keys passed in first argument
     * 
     * @author Mateusz Kleska
     *        
     * @param array $expectedParams
     *            List of keys that will be preserved
     * @param string $type
     *            Param type (as defined in constants)
     * @return \Api\Framework\Basic\Objects\Request
     */
    public function trimToExpectedParams($expectedParams = NULL, $type = self::TYPE_POST)
    {
        if (is_null($expectedParams)) {
            $expectedParams = is_array($this->expectedParams) ? $this->expectedParams : NULL;
        } elseif (! is_array($expectedParams) && empty($expectedParams)) {
            $expectedParams = NULL;
        }
        
        if (empty($this->params) || empty($this->params[$type]) || (is_null($expectedParams))) {
            return $this;
        }
        $this->params[$type] = array_intersect_key($this->params[$type], array_flip($expectedParams));
        return $this;
    }

    /**
     * Check if request is valid
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return boolean
     */
    public function isValid($forceRefresh = false)
    {
        if (is_null($this->valid) || $forceRefresh) {
            $this->valid = $this->validate();
        }
        return $this->valid;
    }

    /**
     * Validate request
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return boolean
     */
    public function validate()
    {
        return true;
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @param string $name            
     * @param mixed $value            
     * @param string $type
     *            Param type (as defined in constants)
     * @return \Api\Framework\Basic\Objects\Request
     */
    public function setParam($name, $value, $type = self::TYPE_POST)
    {
        switch ($type) {
            case self::TYPE_GET:
            case self::TYPE_POST:
                $this->params[$type][$name] = $value;
                break;
        }
        return $this;
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @param string $name            
     * @param mixed $value            
     * @param string $type
     *            Param type (as defined in constants)
     * @return \Api\Framework\Basic\Objects\Request
     */
    public function appendParam($name, $value, $type = self::TYPE_POST)
    {
        switch ($type) {
            case self::TYPE_GET:
            case self::TYPE_POST:
                $curParam = $this->getParam($name, $type);
                if (is_null($curParam)) {
                    $this->setParam($name, $value, $type);
                } elseif (is_array($curParam) && is_array($value)) {
                    $this->setParam($name, array_merge($curParam, $value), $type);
                }
                break;
        }
        return $this;
    }

    /**
     * Get all params of passed type, returns empty array if params of that type doesn't exists
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $type
     *            Param type (as defined in constants)
     * @return mixed
     */
    public function getParams($type = self::TYPE_POST)
    {
        return (isset($this->params[$type]) ? $this->params[$type] : array());
    }

    /**
     * Gets param, can handle param name in formats described below:<br><ul>
     * <li>Input name (e.g.
     * 'User[test][test2]')</li>
     * <li>Array key (e.g. 'test_key')</li>
     * <li>Array of keys (e.g. array('test_key_1', 'test_key_2', 'test_key_3')</li>
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param mixed $name            
     * @param string $type
     *            Param type (as defined in constants)
     * @return mixed
     */
    public function getParam($name, $type = self::TYPE_POST)
    {
        if (is_string($name)) {
            if (preg_match('/\[.+\]/', $name)) {
                return $this->getParamByInputName($name, $type);
            } else {
                return $this->getParamByKey($name, $type);
            }
        } elseif (is_array($name)) {
            return $this->getParamByKeysArray($name, $type);
        }
        return NULL;
    }

    /**
     * Get param using array key
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $key            
     * @param string $type
     *            Param type (as defined in constants)
     * @return mixed
     */
    protected function getParamByKey($key, $type)
    {
        return (isset($this->params[$type][$key]) ? $this->params[$type][$key] : NULL);
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @param string $name            
     * @param string $type
     *            Param type (as defined in constants)
     * @return mixed
     */
    protected function getParamByInputName($name, $type)
    {
        $currParam = $this->params[$type];
        
        $nestedName = explode('[', str_replace(']', '', $name));
        return $this->getNestedValue($currParam, $nestedName);
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @param unknown $currValue            
     * @param unknown $nextKeys            
     * @return unknown Ambigous unknown>
     */
    protected function getNestedValue(& $currValue, $nextKeys = array())
    {
        if (empty($nextKeys)) {
            return $currValue;
        }
        $currKey = array_shift($nextKeys);
        return isset($currValue[$currKey]) ? $this->getNestedValue($currValue[$currKey], $nextKeys) : NULL;
    }

    /**
     * Get param using array of keys
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $keys            
     * @param string $type
     *            Param type (as defined in constants)
     * @return array
     */
    protected function getParamByKeysArray($keys, $type)
    {
        $return = array();
        foreach ($keys as $key) {
            $return[$key] = $this->getParamByKey($key, $type);
        }
        return $return;
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @param string $fallbackToCurrent            
     * @return Ambigous <unknown, string>|Ambigous <string, NULL>
     */
    public function getEndpoint($fallbackToCurrent = FALSE)
    {
        if ($fallbackToCurrent && empty($this->endpoint)) {
            return $this->getCurrentEndpoint();
        }
        return $this->endpoint;
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @return Ambigous <unknown, string>
     */
    public function getCurrentEndpoint()
    {
        if (! empty($_SERVER['REQUEST_SCHEME'])) {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        } elseif (! empty($_SERVER['SERVER_PORT'])) {
            $scheme = ($_SERVER['SERVER_PORT'] == self::SSL_PORT ? self::HTTPS : self::HTTP);
        } else {
            $scheme = self::HTTP;
        }
        
        $endpoint = "{$scheme}://{$_SERVER['SERVER_NAME']}";
        if (FALSE === ($requestUriWithoutGet = stristr($_SERVER['REQUEST_URI'], '?', TRUE))) {
            $requestUriWithoutGet = $_SERVER['REQUEST_URI'];
        }
        $endpoint .= $requestUriWithoutGet;
        return $endpoint;
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @param string $fallbackToCurrentEndpoint            
     * @return string
     */
    public function prepareUrl($fallbackToCurrentEndpoint = FALSE)
    {
        $getParams = $this->params[self::TYPE_GET];
        return $this->getEndpoint($fallbackToCurrentEndpoint) . (! empty($getParams) ? ("?" . http_build_query($getParams)) : '');
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @param string $return            
     * @return string
     */
    public function debug($return = FALSE)
    {
        $debugMsg = '<pre>';
        $debugMsg .= 'CLASS: ' . get_class($this) . '<br>';
        $debugMsg .= 'param types: ' . implode(", ", array_keys($this->params)) . '. Params: <br><br>';
        foreach ($this->params as $type => $arr) {
            $debugMsg .= "{$type}: ";
            $debugMsg .= print_r($arr, TRUE);
            $debugMsg .= "<br />";
        }
        if ($return) {
            return $debugMsg;
        }
        echo $debugMsg;
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @return mixed
     */
    public function __toString()
    {
        $debugMsg = 'Request of type: ' . get_class($this) . ', ';
        $debugMsg .= 'VALID: ' . ($this->isValid() ? 'YES' : 'NO') . PHP_EOL;
        $debugMsg .= var_export($this->params, true);
        return $debugMsg;
    }

    /**
     * TODO Fill this
     * 
     * @author Mateusz Kleska
     *        
     * @param string $asCsv            
     * @param string $serializeObject            
     * @return array
     */
    public function toLogFormat($asCsv = TRUE, $serializeObject = FALSE)
    {
        $debugMsg = array();
        $debugMsg['date'] = date('Y-m-d H:i:s');
        $debugMsg['remote_ip'] = $_SERVER['REMOTE_ADDR'];
        $debugMsg['endpoint'] = $this->getCurrentEndpoint();
        $debugMsg['getParams'] = $asCsv ? serialize($this->getParams(self::TYPE_GET)) : $this->getParams(self::TYPE_GET);
        $debugMsg['postParams'] = $asCsv ? serialize($this->getParams(self::TYPE_POST)) : $this->getParams(self::TYPE_POST);
        $debugMsg['object'] = NULL;
        if ($serializeObject) {
            $debugMsg['object'] = serialize($this);
        }
        
        if ($asCsv) {
            $debugMsg = str_replace(array(PHP_EOL,"\n","\r"), " ", implode(";", $debugMsg));
        }
        return $debugMsg;
    }
}