<?php
namespace Api\Framework\Basic\Objects;

/**
 * Interface for objects valiable for Response
 * 
 * @author Krzysztof Kalkhoff
 *        
 */
interface ResponseInterface
{

    /**
     * Get content type of response (as defined in Response constants)
     * 
     * @author Krzysztof Kalkhoff
     * @return string
     */
    public function getContentType();

    /**
     * Get string content of container
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @return string
     */
    public function getContent();
}