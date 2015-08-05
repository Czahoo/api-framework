<?php
namespace Api\Framework\Basic\Objects;

use Api\Framework\Errors\Error;

/**
 * Basic JSON container
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class JSON implements ResponseInterface
{

    /**
     * Data which will be parsed to json
     * 
     * @var array
     */
    protected $data;

    /**
     * Ordinary contructor
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param array $data            
     */
    public function __construct($data = array())
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        return json_encode($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getContentType()
    {
        return Response::CONTENT_TYPE_JSON;
    }

    /**
     * Set data
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param array $data            
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Append data
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param array $data            
     */
    public function appendData($data)
    {
        $this->data = array_merge($this->data, $data);
    }
}