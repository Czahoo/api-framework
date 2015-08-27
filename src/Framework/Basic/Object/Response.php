<?php
namespace Api\Framework\Basic\Object;

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
     * If response is empty (no content)
     * 
     * @var boolean
     */
    protected $empty;

    /**
     * Contructor
     *
     * @author Krzysztof Kalkhoff
     *        
     * @param string $content            
     */
    public function __construct($content = null)
    {
        $this->httpCode = self::HTTP_CODE_SUCCESS;
        $this->setContentType(self::CONTENT_TYPE_HTML);
        $this->empty = is_null($content);
        
        if (is_null($content)) {
            $this->content = '';
        } elseif (is_object($content) && $object instanceof ResponseInterface) {
            $this->setObject($content);
        } else {
            $this->content = (string) $content;
        }
    }
    
    /**
     * 
     * @return boolean
     */
    public function isEmpty() {
        return $this->empty;
    }

    /**
     * Set container for response content
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param ResponseInterface $obj            
     * @return Response
     */
    public function setObject(ResponseInterface $obj)
    {
        $this->object = $obj;
        $this->setContent($obj->getContent());
        $this->setContentType($obj->getContentType());
        return $this;
    }

    /**
     * Set response content type
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $type            
     * @return Response
     */
    protected function setContentType($type)
    {
        $this->headers[] = "Content-Type: {$type}";
        return $this;
    }

    /**
     * Set content of response
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param string $content            
     * @return Response
     */
    protected function setContent($content)
    {
        $this->empty = empty($content);
        $this->content = $content;
        return $this;
    }

    /**
     * Get content of response
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set http code for response
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param int $code            
     * @return Response
     */
    public function setHttpCode($code)
    {
        $this->httpCode = $code;
        return $this;
    }

    /**
     * Get http code of response
     * 
     * @author Krzysztof Kalkhoff
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Get array of headers
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}