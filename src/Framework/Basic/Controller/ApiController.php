<?php
namespace Api\Framework\Basic\Controller;

use Api\Framework\Basic\Utility\Logger;
use Api\Framework\Basic\Object\Request;
use Api\Framework\Basic\Object\Response;

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
        $this->request = Request::createFromGlobals();
    }

    /**
     * Get request object
     * 
     * @return Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Get response object
     * 
     * @return Response
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

    protected function respond($response = NULL) {
        if(is_null($response)) {
            $response = $this->getResponse();
        }
        
        return $response;
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
