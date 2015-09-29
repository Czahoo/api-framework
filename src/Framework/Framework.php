<?php
use Api\Framework\Basic\Traits\Singleton;
use Api\Framework\Basic\Object\Server;
use Api\Framework\Utility\Helper\Formatter;
use Api\Framework\Basic\Object\Response;

/**
 * Framework class handling routing and translating URL
 * 
 * @author Krzysztof Kalkhoff
 *        
 */
class Framework
{
    use Singleton;

    const LANG_POLISH = "pl";

    const LANG_ENGLISH = "en";

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
    
    const BASE_FOLDER = "Api";
    
    const APP_FOLDER = "App";
    
    const TEMPLATES_FOLDER = "Template";
    
    const APP_CONFIG_FILENAME = "Config.php";
    
    const ROUTING_FILENAME = "Routing.php";

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
        $this->basePath = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.self::BASE_FOLDER.DIRECTORY_SEPARATOR;
        $this->loadConfig();
    }

    private function getRoutingPath() {
        return $this->basePath.self::APP_FOLDER.DIRECTORY_SEPARATOR.self::ROUTING_FILENAME;
    }
    
    private function getConfigPath() {
        return $this->basePath.self::APP_FOLDER.DIRECTORY_SEPARATOR.self::APP_CONFIG_FILENAME;
    }
    
    private function loadConfig()
    {
        $path = $this->getConfigPath();
        $template_path = __DIR__.DIRECTORY_SEPARATOR.self::TEMPLATES_FOLDER.DIRECTORY_SEPARATOR.self::APP_CONFIG_FILENAME;
        if (file_exists($path)) {
            require_once $path;
        } elseif(file_exists($template_path)) {
            copy($template_path, $path);
            require_once $path;
        } else {
            self::debug("Can't load nor create config file");
        }
    }
    
    private function loadRouting()
    {
        $path = $this->getRoutingPath();
        $template_path = __DIR__.DIRECTORY_SEPARATOR.self::TEMPLATES_FOLDER.DIRECTORY_SEPARATOR.self::ROUTING_FILENAME;
        if (file_exists($path)) {
            require $path;
            return $FRAMEWORK_ROUTING;
        } elseif(file_exists($template_path)) {
            copy($template_path, $path);
            self::debug("You must fill default controller in routing file at ".$path);
        } else {
            self::debug("Can't load nor create routing file");
        }
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
            $routing = $this->loadRouting();
        } else {
            if (! file_exists($this->routingPath)) {
                self::debug("You must create routing file at " . $this->routingPath);
            }
            require $this->routingPath;
            $routing = $FRAMEWORK_ROUTING;
        }

        return $routing;
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
        
        self::debug("Fatal error: Controller ({$controllerName}) not found");
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
        
        self::debug("Fatal error: no default method to run in " . get_class($controller));
    }
    
    /**
     * Function used for handling situation that shouldn't happen
     *
     * @param string $msg
     */
    public static function debug($msg)
    {
        if (!defined("SERVER_LIVE") || (defined("SERVER_LIVE") && !SERVER_LIVE)) {
            $return = "Requested URL: " . Server::getAbsoluteURL() . "<br>";
            $return .= $msg . "<br>";
            $return .= "<pre>";
            $return .= print_r(debug_backtrace(), true);
            $return .= "</pre>";
            self::getInstance()->outputContent($return);
        } else {
            trigger_error($msg, E_USER_ERROR);
        }
    }
    
    private function outputContent($content)
    {
        if (is_string($content) || is_numeric($content)) {
            $content = new Response($content);
        } elseif (is_object($content)) {
            if (! ($content instanceof Response)) {
                self::debug("Object returned in controller must be instance of Response");
            }
        } elseif (is_array($content)) {
            $content = new Response(print_r($content, true));
        } else {
            $content = new Response();
        }
    
        foreach ($content->getHeaders() as $header) {
            header($header);
        }
    
        http_response_code($content->getHttpCode());
    
        echo $content->getContent();
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
        $autoloader = new Autoloader(null, $self->basePath);
        $autoloader->register();
        
        $controller = $self->getControllerToRun();
        $method = $self->getMethodToRun($controller);
        
        // First run optional functions
        if (method_exists($controller, 'beforeRun'))
            $controller->beforeRun();
        
        if (method_exists($controller, 'beforeMethodRun'))
            $controller->beforeMethodRun();
        
        // If response was set in "before" functions, output it instead of method return value
        if (! $controller->getResponse()->isEmpty()) {
            return $self->outputContent($controller->getResponse());
        }
            
        $self->normalizeMethodArguments($controller, $method);
        // Get content
        $content = call_user_func_array(array($controller,$method), $self->methodArguments);
        
        // After running optional functions
        if (method_exists($controller, 'afterRun'))
            $controller->afterRun();
        
        $self->outputContent($content);
    }
    
    /**
     *
     * @param Controller $controller
     * @param string $method
     */
    private function normalizeMethodArguments($controller, $method)
    {
        $reflection = new ReflectionMethod($controller, $method);
        $numRequired = $reflection->getNumberOfRequiredParameters();
        $numCurrent = count($this->methodArguments);
        if ($numRequired > $numCurrent) {
            // If request don't have enought arguments for method set it as invalid
            $controller->getRequest()->setValid(false);
            for ($i = ($numRequired - $numCurrent); $i --; $i > 0) {
                $this->methodArguments[] = NULL;
            }
        }
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
            $params = Formatter::cleanData($params);
            
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
     * Get language (default "pl")
     *
     * @author Krzysztof Kalkhoff
     *
     */
    public static function getLang()
    {
        return self::getInstance()->lang;
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
        $_SESSION[self::LAST_PAGE_SESSION_NAME] = Server::getAbsoluteURL();
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