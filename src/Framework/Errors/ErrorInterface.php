<?php
namespace Api\Framework\Errors;

interface ErrorInterface
{

    /**
     * Get type of error
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public function getType();

    /**
     * Get raw message, by default it should be $message
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public function getRawMessage();

    /**
     * Get HTML representation of error
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public function getHTML();

    /**
     * Add self to list of string representation of errors
     * 
     * @author Krzysztof Kalkhoff
     *        
     * @param array $list            
     * @param string $msgType            
     */
    public function addToList(&$list, $msgType);
}