<?php
namespace Api\Framework\Basic\Object;

/**
 * Handles all $_SERVER info
 *
 * @author Krzysztof Kalkhoff
 *        
 */
class Server
{

    const HTTPS_DISABLED = "off";

    const HTTPS_PORT = 443;

    const DEFAULT_PORT = 80;

    /**
     * If request is using SSL
     *
     * @return boolean
     */
    public static function isSecure()
    {
        return (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== self::HTTPS_DISABLED) || $_SERVER['SERVER_PORT'] == self::HTTPS_PORT;
    }

    /**
     * Get absolute URL of current page
     *
     * @return string
     */
    public static function getDomain()
    {
        $pageURL = self::getProtocol() . "://";
        
        if ($_SERVER["SERVER_PORT"] != self::DEFAULT_PORT) {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"];
        }
        return $pageURL;
    }

    public static function getProtocol()
    {
        return strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/')));
    }

    /**
     * Get relative URL of page
     *
     * @return string
     */
    public static function getRelativeURL()
    {
        return $_SERVER["REQUEST_URI"];
    }

    /**
     * Full URL of requested page
     *
     * @author Krzysztof Kalkhoff
     *        
     * @return string
     */
    public static function getAbsoluteURL()
    {
        return self::getDomain() . self::getRelativeURL();
    }
}