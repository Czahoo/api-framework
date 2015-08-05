<?php
namespace Api\Framework\Basic\Objects;

/**
 * Basic response object used in controllers
 * 
 * @author Krzysztof Kalkhoff
 *        
 */
class Response
{

    const CONTENT_TYPE_HTML = 'text/html';

    const CONTENT_TYPE_JSON = 'application/json';

    const HTTP_CODE_SUCCESS = 200;

    const HTTP_CODE_BAD_REQUEST = 400;

    const HTTP_CODE_NOT_FOUND = 404;

    /**
     * Object implementing ResponseInterface containing response body
     * 
     * @var mixed
     */
    protected $object;

    /**
     * Raw content of response
     * 
     * @var string
     */
    protected $content;

    /**
     * HTTP code of response
     * 
     * @var int
     */
    protected $httpCode;

    /**
     * Array of string headers
     * 
     * @var array
     */
    protected $headers = array();

    /**
     * Ordinary contructor
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $content            
     */
    public function __construct($content = null)
    {
        $this->httpCode = self::HTTP_CODE_SUCCESS;
        $this->setContentType(self::CONTENT_TYPE_HTML);
        
        if (is_null($content)) {
            $this->content = '';
        } elseif (is_object($content) && $object instanceof ResponseInterface) {
            $this->setObject($content);
        } else {
            $this->content = (string) $content;
        }
    }

    public function setObject(ResponseInterface $obj)
    {
        $this->object = $obj;
        $this->setContent($obj->getContent());
        $this->setContentType($obj->getContentType());
    }

    protected function setContentType($type)
    {
        $this->headers[] = "Content-Type: {$type}";
    }

    protected function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setHttpCode($code)
    {
        $this->httpCode = $code;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}