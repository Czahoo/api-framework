<?php
namespace App\Communication\Controller;

use Api\Framework\Basic\Controller\ApiController;
use App\Communication\Object\Service;
use App\Communication\Object\Message;
use App\Communication\Object\Client;
use Api\Framework\Utility\Helper\Formatter;
use Api\Framework\Basic\Object\Request;
use App\Basic\Object\Auth;
use Api\Framework\Basic\Object\Response;

class CommunicationController extends ApiController
{
    protected $validIP = array('127.0.0.1','37.59.14.17','178.32.200.204');
    protected $client;
    
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
        if(!Auth::checkHash($hash, $data)) {
        	   return false;
        }
        return true;
    }
    
    public function beforeRun() {
        $this->request = Request::createFromGlobals(false);
        $this->response = new Response();
        $this->client = new Client();
        
        if(!$this->authenticate()) {
            $msg = new Message();
            $msg->setSuccess(false)->addError('Wrong authentication hash');
            return $this->respondMessage($msg);
        }
    }
    
    public function getClient() {
        return $this->client;
    }
    
    protected function respondMessage(Message $msg) {
        return $this->getResponse()->setContentType(Response::CONTENT_TYPE_JSON)->setContent($this->getClient()->parse($msg));
    }
    
    /*
     * Wywołanie pod tą metodę odpowiada tym samym message który został wysłany
     * Przydatne do weryfikacji, że serwis wysyła i dostaje prawidłowe dane
     */
    public function test()
    {
        return $this->respondMessage($this->getClient()->recieve());
    }
}