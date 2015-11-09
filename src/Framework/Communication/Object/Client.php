<?php
namespace Api\Framework\Communication\Object;

use Curl\Curl;
use Api\Framework\Communication\Utility\AuthenticationInterface;

class Client
{

    const AUTH_PARAM_NAME = "auth";

    const DATA_PARAM_NAME = "data";
    
    /**
     * Stores object for authentication
     * @var AuthenticationInterface
     */
    protected $auth;

    public function __construct(AuthenticationInterface $authentication)
    {
    	$this->auth = $authentication;
    }
    
    /**
     * 
     * @author Krzysztof Kalkhoff
     *
     * @return AuthenticationInterface
     */
    public function getAuth() {
        return $this->auth;
    }

    /**
     *
     * @param Service $service
     * @param Message $message
     * @return Message
     */
    public function send(Service $service, Message $message)
    {
        // Prepare all needed data
        $post = [self::DATA_PARAM_NAME => $this->parse($message)];
        $url = $service->getUrl([
            self::AUTH_PARAM_NAME => $this->auth->getHash($post)
        ]);
        $curl = new Curl();
        // Send data
        $curl->post($url->buildUrl(), $post);
        
        if ($curl->error) {
            $msg = new Message();
            $msg->addError($curl->curl_error_message);
            return $msg;
        }
        
        return $this->decode($curl->response);
    }
    
    public function parse(Message $message) {
        return json_encode($message);
    }

    /**
     * @param string $data
     * @return Message
     */
    protected function decode($data)
    {
        $return = new Message();
        if (! ($decodedData = json_decode($data, true))) {
            $return->addError('Cannot parse data to JSON: ' . print_r($data, true));
        } else {
            $return->initFromData($decodedData);
        }
        return $return->setSuccess(!$return->hasErrors());
    }

    /**
     * @return Message
     */
    public function recieve()
    {
        return $this->decode($_POST[self::DATA_PARAM_NAME]);
    }
}