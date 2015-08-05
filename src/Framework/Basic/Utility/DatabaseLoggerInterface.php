<?php
namespace Api\Framework\Basic\Utility;

/**
 * Inteface used in database storage for logger 
 * @author Krzysztof Kalkhoff
 *
 */
interface DatabaseLoggerInterface {
	/**
	 * Add message to log
	 * @author Krzysztof Kalkhoff
	 *
	 * @param string $msg
	 * @return boolean
	 */
	public function add($msg);
}