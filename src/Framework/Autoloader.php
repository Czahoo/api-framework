<?php

/**
 * Class handling autoloading using PSR-4 standard
 * @author Krzysztof Kalkhoff
 *
 */
class Autoloader
{

    const DEFAULT_FILE_EXTENSION = ".php";

    const DEFAULT_NAMESPACE_SEPARATOR = "\\";
    
    const DEFAULT_DIR_SEPARATOR = "/";
    
    const AUTOLOAD_FUNCTION_NAME = "autoload";

    private $fileExtension;

    private $basicNamespace;

    private $basicPath;

    private $namespaceSeparator;

    /**
     * Creates new autoloader
     * 
     * @author Krzysztof Kalkhoff
     * @param string $namespace
     *            Basic class namespace (ignored in creation of file path)
     * @param string $path
     *            Basic path to search for classes files
     */
    public function __construct($namespace = '', $path = '')
    {
        $this->basicNamespace = $namespace;
        $this->basicPath = ($path == self::DEFAULT_DIR_SEPARATOR ? removeTrailingSlash($path) : $path);
        $this->fileExtension = self::DEFAULT_FILE_EXTENSION;
        $this->namespaceSeparator = self::DEFAULT_NAMESPACE_SEPARATOR;
        if(substr($this->basicPath, -1) === self::DEFAULT_DIR_SEPARATOR || substr($this->basicPath, -1) === DIRECTORY_SEPARATOR) {
            $this->basicPath = substr($this->basicPath, 0, -1);
        }
    }

    /**
     * Sets basic namespace
     * 
     * @author Krzysztof Kalkhoff
     * @param string $namespace            
     */
    public function setBasicNamespace($namespace)
    {
        $this->basicNamespace = $namespace;
    }

    /**
     * Sets basic class search path
     * 
     * @author Krzysztof Kalkhoff
     * @param string $path            
     */
    public function setBasicPath($path)
    {
        $this->basicPath = $path;
    }

    /**
     * Sets classes file extension
     * 
     * @author Krzysztof Kalkhoff
     * @param string $extension            
     */
    public function setFileExtension($extension)
    {
        $this->fileExtension = $extension;
    }

    /**
     * Sets namespace separator
     * 
     * @author Krzysztof Kalkhoff
     * @param string $separator            
     */
    public function setNamespaceSeparator($separator)
    {
        $this->namespaceSeparator = $separator;
    }

    /**
     * Add to autoloader queue
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public function register()
    {
        spl_autoload_register(array($this, self::AUTOLOAD_FUNCTION_NAME));
    }

    /**
     * Remove from autoloader queue
     * 
     * @author Krzysztof Kalkhoff
     *        
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, self::AUTOLOAD_FUNCTION_NAME));
    }

    /**
     * Loads class or interface
     * 
     * @author Krzysztof Kalkhoff
     * @param string $className
     *            Name of the class to load
     */
    public function autoload($className)
    {
        $class = $className;
        $filename = '';
        
        if (! empty($this->basicNamespace)) {
            if (mb_strtolower(substr($className, 0, strlen($this->basicNamespace . $this->namespaceSeparator))) === mb_strtolower($this->basicNamespace . $this->namespaceSeparator)) {
                // Cut basic namespace
                $class = substr($className, strlen($this->basicNamespace . $this->namespaceSeparator));
            } else {
                // Can't load namespace
                return false;
            }
        }
        // Extract namespace and class
        if (false !== ($namespacePosition = strripos($class, $this->namespaceSeparator))) {
            $namespace = substr($class, 0, $namespacePosition);
            $class = substr($class, $namespacePosition + 1);
            $filename = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        
        $filename .= $class . $this->fileExtension;
        $path = (!empty($this->basicPath) ? $this->basicPath . DIRECTORY_SEPARATOR : '') . $filename;
        
        if (file_exists($path)) {
            require $path;
            return true;
        }
        
        return false;
    }
}