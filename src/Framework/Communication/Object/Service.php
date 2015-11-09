<?php
namespace Api\Framework\Communication\Object;

use Api\Framework\Utility\Helper\Validation;
use Api\Framework\Basic\Object\URL;

class Service
{
    const INTERNAL_API_SUFFIX = "internal_api/";
    
    const TYPE_DEVELOP = "develop";
    
    const TYPE_PRODUCTION = "production";

    protected $address, $useInternalAPI;

    public function __construct($address, $method = '', $interalApi = true)
    {
        $this->setAddress($address)
            ->setMethod($method)
            ->setInternalApi($interalApi);
    }

    /**
     * Set service address<br>
     * I.e. http://test.com/
     * @author Krzysztof Kalkhoff
     *
     * @param string $address
     * @return Service
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Set method for service call<br>
     * I.e. user/register
     * @author Krzysztof Kalkhoff
     *
     * @param string $method
     * @return Service
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * If this will be internal API request<br>
     * For internal api request address is slighly modified
     * @author Krzysztof Kalkhoff
     *
     * @param boolean $bool
     * @return Service
     */
    public function setInternalApi($bool)
    {
        $this->useInternalAPI = $bool;
        return $this;
    }

    /**
     * Get service address
     * @author Krzysztof Kalkhoff
     * 
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Get service method
     * @author Krzysztof Kalkhoff
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get Url object for current address, method and internalApi params<br>
     * @author Krzysztof Kalkhoff
     *
     * @param array $query additional params appended to query
     * @return URL
     */
    public function getUrl($query = [])
    {
        $address = $this->getAddress();
        if ($this->useInternalAPI) {
            $address .= self::INTERNAL_API_SUFFIX;
        }
        $address .= $this->getMethod();
        $url = new URL($address);
        return empty($query) ? $url : $url->appendQuery($query);
    }

    /**
     * Returns service address if proper config exists, or empty string otherwise
     * @author Krzysztof Kalkhoff
     *
     * @param string $service Name of service in config
     * @param string $type Type of service as defined in class constants
     * @return string
     */
    public static function getServiceAddress($service, $type = NULL)
    {
        global $SERVICES_CONFIG;
        if(empty($SERVICES_CONFIG) || (is_null($type) && !defined("SERVER_LIVE"))) {
            return "";
        }
        $type = is_null($type) ? (SERVER_LIVE ? self::TYPE_PRODUCTION : self::TYPE_DEVELOP) : $type;
        return isset($SERVICES_CONFIG[$service][$type]) ? $SERVICES_CONFIG[$service][$type] : "";
    }
}