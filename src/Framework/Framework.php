<?php
use Basic\Controller\ApiController;

/**
 * Framework class handling routing and translating URL
 * 
 * @author Krzysztof Kalkhoff
 *        
 */
class Framework
{
    use Singleton, AutoGetters;

    const LANG_POLISH = "pl";

    const LANG_ENGLISH = "eng";

    const LOG_SESSION_NAME = "Log";

    const LAST_PAGE_SESSION_NAME = "LastPage";

    const PARAM_LANG = "lang";

    const PARAM_CONTROLLER = "controller";

    const PARAM_METHOD = "method";

    const PARAM_METHOD_ARGUMENTS = "method_arguments";

    const PARAM_PAGE = "page";

    const DEFAULT_CONTROLLER_ROUTE_NAME = "default_controller";

    const DEFAULT_CONTROLLER_ACTION = "show";

    const CONTROLLER_SUFFIX = "Controller";

    const API_TYPE_EXTERNAL = "external";

    const API_TYPE_INTERNAL = "internal";

    /**
     * Application language
     * 
     * @var string
     */
    public $lang;

    /**
     * Type of api (internal or external)
     * 
     * @var string
     */
    private $apiType;

    /**
     * Requested url
     * 
     * @var string
     */
    private $requestedUrl;

    /**
     * Params created as result of parsing url
     * 
     * @var array
     */
    private $urlParams;

    /**
     * Arguments of method to call in Controller
     * 
     * @var array
     */
    private $methodArguments;

    /**
     * Name of controller to run
     * 
     * @var string
     */
    private $controller;

    /**
     * Name of method to run on controller
     * 
     * @var string
     */
    private $action;

    /**
     * Base path to application
     *
     * @var string
     */
    private $basePath;
    
    /**
     * Path to file with routing
     *
     * @var string
     */
    private $routingPath;
    
    /**
     * Initialize function from singleton trait
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    protected function init()
    {
        $this->urlParams = array();
        $this->methodArguments = array();
        $this->lang = self::LANG_POLISH;
        $this->basePath = $_SERVER['DOCUMENT_ROOT'].BASE_APPLICATION_FOLDER;
    }

    /**
     * Get routing array<br><br>
     * Array must be formated as defined:<br>
     * $FRAMEWORK_ROUTING = array(<br>
     * Framework::API_TYPE_EXTERNAL => array(<br>
     * Framework::DEFAULT_CONTROLLER_ROUTE_NAME => '{DefaultControllerNameWithNamespace}',<br>
     * ),<br>
     * Framework::API_TYPE_INTERNAL => array(<br>
     * Framework::DEFAULT_CONTROLLER_ROUTE_NAME => '{DefaultControllerNameWithNamespace}',<br>
     * ),<br>
     * );<br>
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return array
     */
    private function getRouting()
    {
        if(empty($this->routingPath)) {
            $this->routingPath = $this->basePath.ROUTING_PATH;
        }

        if (! file_exists($this->routingPath)) {
            debug("You must create routing file at " . $this->routingPath);
        }
        require_once $this->routingPath;
        
        return $FRAMEWORK_ROUTING;
    }

    /**
     * Gets name of controller to run.<br>
     * Using routing file located at ROUTING_PATH
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return string
     */
    private function getControllerToRun()
    {
        $routing = $this->getRouting();
        $module = strtolower($this->urlParams[self::PARAM_CONTROLLER]);
        
        $module = strtr($module, '-', '_');
        
        // Check if we have routing to this module
        if (isset($routing[$this->apiType][$module])) {
            $controllerName = $routing[$this->apiType][$module];
        } else {
            // Call default controller
            $controllerName = $routing[$this->apiType][self::DEFAULT_CONTROLLER_ROUTE_NAME];
        }
        
        if (class_exists($controllerName)) {
            return new $controllerName();
        }
        
        debug("Fatal error: Controller ({$controllerName}) not found");
    }

    /**
     * Gets method name to run on controller.<br>
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $controller            
     * @return string
     */
    private function getMethodToRun($controller)
    {
        $method = $this->urlParams[self::PARAM_METHOD];
        // Try loading requested method
        if (! empty($method)) {
            $methodClean = strtr($method, '-', '_');
            if (method_exists($controller, $methodClean)) {
                return $methodClean;
            } else {
                // Add it to function arguments
                array_unshift($this->methodArguments, $method);
            }
        }
        
        // Try default
        if (method_exists($controller, self::DEFAULT_CONTROLLER_ACTION)) {
            return self::DEFAULT_CONTROLLER_ACTION;
        }
        
        debug("Fatal error: no default method to run in " . get_class($controller));
    }

    /**
     * Outputs content returned from controller
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param mixed $view            
     */
    private function outputView($view)
    {
        if (is_string($view) || is_numeric($view)) {
            echo $view;
        } elseif (is_object($view)) {
            $method = self::DEFAULT_CONTROLLER_ACTION;
            if (method_exists($view, $method)) {
                echo $view->$method();
            } else {
                echo (string) $view;
            }
        } elseif (is_array($view)) {
            print_r($view);
        }
    }

    /**
     * Get time taken to handle request
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return number
     */
    public static function getTimer()
    {
        return microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
    }

    /**
     * Main method that runs controller and method depending on params passed in url
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public static function run()
    {
        $self = self::getInstance();
        
        // Register autoloader
        $autoloader = new Autoloader(null, BASE_APPLICATION_FOLDER);
        $autoloader->register();
        
        $controller = $self->getControllerToRun();
        $method = $self->getMethodToRun($controller);
        
        // First run optional functions
        if (method_exists($controller, 'beforeRun'))
            $controller->beforeRun();
        
        if (method_exists($controller, 'beforeMethodRun'))
            $controller->beforeMethodRun();
            
            // Get view
        $view = call_user_func_array(array($controller,$method), $self->methodArguments);
        
        // After running optional functions
        if (method_exists($controller, 'afterRun'))
            $controller->afterRun();
        
        $self->outputView($view);
    }
    
    /**
     * Set path to routing file
     * @author Krzysztof Kalkhoff
     *
     * @param string $path
     */
    public static function setRouting($path) {
        self::getInstance()->routingPath = $path;
    }

    /**
     * Translate url passed from htaccess
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $url            
     */
    public static function translateUrl($url)
    {
        $self = self::getInstance();
        
        $self->requestedUrl = $url;
        $params = self::getParamsFromURL($url);
        foreach ($params as $name => $value) {
            switch ($name) {
                case self::PARAM_METHOD_ARGUMENTS:
                    $self->methodArguments = $value;
                    break;
                
                case self::PARAM_LANG:
                    $self->lang = $value;
                    break;
                
                default:
                    $self->urlParams[$name] = $value;
                    break;
            }
        }
    }

    /**
     * Detect api context (internal or external)
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $apiType            
     */
    public static function detectContext($apiType = null)
    {
        if (is_null($apiType) || ($apiType != self::API_TYPE_INTERNAL && $apiType != self::API_TYPE_EXTERNAL)) {
            $apiType = self::API_TYPE_EXTERNAL;
        }
        self::getInstance()->apiType = $apiType;
    }

    /**
     * Parse url given by .
     * htaccess to framework params
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $url            
     * @return array
     */
    public static function getParamsFromURL($url)
    {
        $return = array();
        if (! empty($url)) {
            $params = explode("/", $url);
            $params = cleanData($params);
            
            if (count($params) != 0) {
                foreach ($params as $nr => $param) {
                    if ((preg_match('/page-(?<number>\d+)/', $param, $matches))) { // Page
                        $return[self::PARAM_PAGE] = (int) $matches[1];
                    } elseif ((preg_match('/lang-(?P<value>.+)/', $param, $matches))) { // Language
                        $return[self::PARAM_LANG] = $matches['value'];
                    } elseif (! isset($return[self::PARAM_CONTROLLER])) { // Module
                        $return[self::PARAM_CONTROLLER] = $param;
                    } elseif (! isset($return[self::PARAM_METHOD])) { // Method
                        $return[self::PARAM_METHOD] = $param;
                    } else { // Method argument
                        if (! array_key_exists(self::PARAM_METHOD_ARGUMENTS, $return)) {
                            $return[self::PARAM_METHOD_ARGUMENTS] = array();
                        }
                        $return[self::PARAM_METHOD_ARGUMENTS][] = $param;
                    }
                }
            }
        }
        
        return $return;
    }

    /**
     * Get framework params
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public static function getParams()
    {
        return self::getInstance()->methodArguments;
    }

    /**
     * Get controller method to call
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public static function getMethod()
    {
        return ! empty(self::getInstance()->urlParams[self::PARAM_METHOD]) ? self::getInstance()->urlParams[self::PARAM_METHOD] : self::DEFAULT_CONTROLLER_ACTION;
    }

    /**
     * Get controller to run
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public static function getController()
    {
        return self::getInstance()->urlParams[self::PARAM_CONTROLLER];
    }

    /**
     * Get page for pagination (default 1)
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public static function getPage()
    {
        return ! empty(self::getInstance()->urlParams[self::PARAM_PAGE]) && self::getInstance()->urlParams[self::PARAM_PAGE] > 0 ? self::getInstance()->urlParams[self::PARAM_PAGE] : 1;
    }

    /**
     * Get relative url of current call
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return string
     */
    public static function getRequestedURL()
    {
        return self::getInstance()->requestedUrl;
    }

    /**
     * Get last visited page address (relative)
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return unknown
     */
    public static function getLastPageAddress()
    {
        return $_SESSION[self::LAST_PAGE_SESSION_NAME];
    }

    /**
     * Save common data stored between requests
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public static function saveCommonData()
    {
        // Save needed data
        $_SESSION[self::LAST_PAGE_SESSION_NAME] = currentPageURL();
    }

    /**
     * Redirect to address
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $address            
     */
    public static function redirect($address)
    {
        self::saveCommonData();
        header("Location: {$address}");
        exit();
    }

    /**
     * Check if request is made by ajax call
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return boolean
     */
    public static function isAjax()
    {
        return ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}