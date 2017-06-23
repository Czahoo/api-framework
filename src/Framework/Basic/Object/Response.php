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
    
    const CONTENT_TYPE_CSS = 'text/css';

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
    protected $empty = true;

    /**
     * Contructor
     *
     * @author Krzysztof Kalkhoff
     *        
     * @param string $content            
     */
    public function __construct($content = '', $contentType = NULL, $httpCode = NULL)
    {
        if (is_object($content) && $object instanceof ResponseInterface) {
            $this->setObject($content);
        } else {
            $this->setContent((string) $content);
        }
        
        $this->setContentType((is_null($contentType) ? self::CONTENT_TYPE_HTML : $contentType));
        $this->setHttpCode((is_null($httpCode) ? self::HTTP_CODE_SUCCESS : $httpCode));
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
     * @param boolean $append         
     * @return Response
     */
    public function setContentType($type, $append = false)
    {
        $contentType = "Content-Type: {$type}";
        $this->setHeader($contentType, $append);
        return $this;
    }

    /**
     * Adds or replace headers array
     *
     * @param string|array $header
     */
    public function setHeader($header, $append = true)
    {
        if ($append) {
            if (is_string($header)) {
                $this->headers[] = $header;
            } elseif (is_array($header)) {
                $this->headers = array_merge($this->headers, $header);
            } else {
                \Framework::debug("Invalid header type");
            }
        } else {
            if (is_string($header)) {
                $header = array($header);
            }
            if (is_array($header)) {
                $this->headers = $header;
            } else {
                \Framework::debug("Invalid header type");
            }
        }
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
    public function setContent($content)
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