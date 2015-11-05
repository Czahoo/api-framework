<?php
namespace App\Communication\Controller;

use Api\Framework\Basic\Controller\ApiController;
use Api\Framework\Communication\Object\Service;
use Api\Framework\Communication\Object\Message;
use Api\Framework\Communication\Object\Client;
use Api\Framework\Utility\Helper\Formatter;
use Api\Framework\Basic\Object\Request;
use Api\Framework\Basic\Object\Response;

abstract class CommunicationController extends ApiController
{
    const DEFAULT_VALID_IP_ADDRESS = "127.0.0.1";
    protected $validIP;
    protected $client;
    
    public function __construct() {
        global $VALID_IP_ADDRESSES;
        $this->validIP = (empty($VALID_IP_ADDRESSES)) ? [self::DEFAULT_VALID_IP_ADDRESS] : $VALID_IP_ADDRESSES;
    }
    
    protected function authenticate() {
        $hash = Formatter::cleanData($this->getRequest()->getParam(Client::AUTH_PARAM_NAME, Request::TYPE_GET));
        $data = $this->getRequest()->getParams(Request::TYPE_POST);
        return $this->doAuthenticate($hash, $data);
    }
    
    protected function doAuthenticate($hash, $data = NULL)
    {
        if(!$this->getRequest()->isValid()) {
            return false;
        }
    
        if(!in_array($_SERVER['REMOTE_ADDR'], $this->validIP)) {
            return false;
        }
        if(!$this->getClient()->getAuth()->checkHash($hash, $data)) {
        	   return false;
        }
        return true;
    }
    
    public function beforeRun() {
        $this->request = Request::createFromGlobals(false);
        $this->response = new Response();
        
        if(!$this->authenticate()) {
            $msg = new Message();
            $msg->setSuccess(false)->addError('Wrong authentication hash');
            return $this->respondMessage($msg);
        }
    }
    
    /**
     * 
     * @author Krzysztof Kalkhoff
     *
     * @return \Api\Framework\Communication\Object\Client
     */
    abstract public function getClient();
    
    protected function respondMessage(Message $msg) {
        return $this->getResponse()->setContentType(Response::CONTENT_TYPE_JSON)->setContent($this->getClient()->parse($msg));
    }
}