<?php
namespace Api\Framework\Basic\Object;

class URL
{

    const PARAM_SCHEME = "scheme";

    const PARAM_HOST = "host";

    const PARAM_PORT = "port";

    const PARAM_USER = "user";

    const PARAM_PASS = "pass";

    const PARAM_PATH = "path";

    const PARAM_QUERY = "query";

    const PARAM_FRAGMENT = "fragment";
    
    protected $rawUrl, $url, $query, $page = NULL;

    public function __construct($url)
    {
        $this->rawUrl = $url;
        $this->url = $this->parseUrl($url);
    }

    protected function parseUrl($url)
    {
        if (! empty($url) && ($parsedUrl = parse_url($url)) !== false) {
            $query = array();
            parse_str($parsedUrl[self::PARAM_QUERY], $query);
            $this->query = $query;
            return $parsedUrl;
        } else {
            debug("Malformed URL");
        }
    }
    
    public function buildUrl() {
        $queryString = http_build_query($this->query);
        $scheme   = $this->url[self::PARAM_SCHEME];
        $host     = $this->url[self::PARAM_HOST];
        $port     = $this->url[self::PARAM_PORT];
        $user     = $this->url[self::PARAM_USER];
        $pass     = $this->url[self::PARAM_PASS];
        $path     = $this->url[self::PARAM_PATH];
        $query    = $queryString;
        $fragment = $this->url[self::PARAM_FRAGMENT];
        
        $userinfo  = !strlen($pass) ? $user : "$user:$pass";
        $host      = !"$port" ? $host : "$host:$port";
        $authority = !strlen($userinfo) ? $host : "$userinfo@$host";
        $hier_part = !strlen($authority) ? $path : "//$authority$path";
        $url       = !strlen($scheme) ? $hier_part : "$scheme:$hier_part";
        $url       = is_null($this->page) ? $url : $url."/page-{$this->page}/";
        $url       = !strlen($query) ? $url : "$url?$query";
        $url       = !strlen($fragment) ? $url : "$url#$fragment";
        
        return $url;
    }

    public function setPage($page) 
    {
        $this->page = max([intval($page), 1]);
    }
    
    public function appendQuery($query)
    {
        $append = array();
    	if(is_string($query)) {
    	    parse_str($query, $append);
    	} elseif(is_array($query)) {
    	    $append = array_merge($append, $query);
    	}
    	$this->query = array_merge($this->query, $append);
    	
    	return $this;
    }
    
    public function __toString() {
        return $this->buildUrl();
    }
}