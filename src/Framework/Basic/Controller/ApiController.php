<?php
namespace Api\Framework\Basic\Controller;

use Api\Framework\Basic\Utility\Logger;
use Api\Framework\Basic\Objects\Request;
use Api\Framework\Basic\Objects\Response;

/**
 * Basic Controller for Api requests
 *
 * @author Krzysztof Kalkhoff
 *        
 */
abstract class ApiController
{

    const DEFAULT_LOG_FILE = "Api/Logs/log.txt";

    const LOGGER_INSTANCE_KEY = "api_logs";

    /**
     * Request object
     *
     * @var Request
     */
    protected $request;

    /**
     * Response object
     *
     * @var Response
     */
    protected $response;

    /**
     * Function called before any method in controller
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public function beforeRun()
    {
        $this->response = new Response();
        $this->request = new Request();
        $this->request->initFromGlobals();
    }

    /**
     * Get request object
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return \Api\Framework\Basic\Objects\Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Get response object
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    protected function getResponse()
    {
        return $this->response;
    }

    /**
     * Default function to call in controller
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public function show()
    {
        debug('This method must be overwritten');
    }

    /**
     * Authenticate incoming request
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return boolean
     */
    protected function authenticate()
    {
        return $this->getRequest()->validate();
    }

    /**
     * Log incoming request to file
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    protected function logRequestToFile()
    {
        Logger::initStatic(self::LOGGER_INSTANCE_KEY, self::DEFAULT_LOG_FILE, TRUE, TRUE);
        Logger::logStatic(self::LOGGER_INSTANCE_KEY, $this->getRequest()->toLogFormat(FALSE));
    }

    /**
     * Log incoming request to Database
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    protected function logRequestToDb(DatabaseLoggerInterface $object)
    {
        // TODO Fill this
    }
}
