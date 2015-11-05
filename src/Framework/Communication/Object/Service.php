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

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setInternalApi($bool)
    {
        $this->useInternalAPI = $bool;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getMethod()
    {
        return $this->method;
    }

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