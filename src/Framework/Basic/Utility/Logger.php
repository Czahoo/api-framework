<?php
namespace Api\Framework\Basic\Utility;

use Api\Framework\Utility\Helper\FileHelper;
use Api\Framework\Utility\Helper\Formatter;
/************************************** TODO: WRITE PHPDOC **************************************/ 
class Logger
{

    const BASE_LOG_FOLDER = 'var/log/api/';

    const CLEAN_TRUNCATE_LOGFILE = 'log_clean_truncate.log';

    const TRUNCATE_LOGSIZE_FACTOR = 0.6; // do jakiej czesci self::FILE_MAX_SIZE zmniejszamy wielkie logi
    const FILE_EXPIRATION_TIME = 2592000;

    const FILE_MAX_SIZE = 104857600; // 100 MB
    protected $logFolder = '';

    protected $logFilename = NULL;

    protected $settingsCheckedOK = FALSE;

    protected static $staticInstance = NULL;

    /**
     * ========================= STATIC FUNCTIONS ==========================
     */
    
    /**
     *
     * @param string $logPath
     *            filepath including filename
     * @param boolean $logPathAbsolute
     *            by default provided $logPath is relative to BASE_LOG_FOLDER
     * @param boolean $useFileDateSuffix
     *            files created will be with date suffix in the end (i.e. temp.log will become temp_20140911.log)
     * @return Logger returns new logger object with everything set to use @see log() function
     */
    public static function newLogger($logPath, $logPathAbsolute = FALSE, $useFileDateSuffix = TRUE)
    {
        $logger = new self();
        $logFilename = pathinfo($logPath, PATHINFO_BASENAME);
        $folder = pathinfo($logPath, PATHINFO_DIRNAME);
        if ($logPathAbsolute) {
            if ($folder === '.') {
                $folder = '';
            }
            $logFilename = pathinfo($logPath, PATHINFO_BASENAME);
        } elseif (empty($folder) || $folder == '.') {
            $folder = NULL;
        }
        $logger->setLogFolder($folder, $logPathAbsolute);
        $logger->setLogFilename($logFilename, $useFileDateSuffix);
        return $logger;
    }

    /**
     * Initiates a static object under instance with specified settings
     *
     * @param string $instanceKey
     *            an instance key tells which logger static instance should be used to log (beforehands created, if not existent, with default settings)
     * @param string $logPath            
     * @param boolean $logPathAbsolute            
     * @param boolean $useFileDateSuffix            
     */
    public static function initStatic($instanceKey, $logPath = NULL, $logPathAbsolute = FALSE, $useFileDateSuffix = FALSE)
    {
        if (isset(self::$staticInstance[$instanceKey])) {
            // trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' called with already existing $instanceKey (' . $instanceKey . '), no overwrite performed', E_WARNING);
            return FALSE;
        }
        
        self::$staticInstance[$instanceKey] = self::newLogger($logPath, $logPathAbsolute, $useFileDateSuffix);
    }

    /**
     *
     * @param string $instanceKey            
     * @see Logger::initStatic()
     * @param string $msg
     *            message to store in string format
     * @param mixed $addData
     *            additional data that will be appended by @see print_r function
     */
    public static function logStatic($instanceKey, $msg, $addData = NULL)
    {
        if (is_null(self::$staticInstance[$instanceKey])) {
            self::initStatic($instanceKey, "$instanceKey.log");
        }
        self::$staticInstance[$instanceKey]->log($msg, $addData);
    }

    public static function quickLog($msg, $addData = NULL)
    {
        $instanceKey = 'quicklog';
        self::initStatic($instanceKey, "$instanceKey.log", FALSE, FALSE);
        self::logStatic($instanceKey, $msg, $addData);
    }

    /**
     * ========================= NON-STATIC FUNCTIONS ==========================
     */
    public function log($msg, $addData = NULL)
    {
        if (! $this->checkSettings(FALSE)) {
            throw new Exception('Logger settings incorrect');
        }
        if (is_array($msg)) {
            $msg = print_r($msg, true);
        }
        FileHelper::makeDir($this->getDocumentRoot() . DIRECTORY_SEPARATOR . $this->logFolder);
        $loggedMsg = date('Y-m-d H:i:s') . ' : ' . $msg;
        if (! is_null($addData)) {
            $loggedMsg .= '; addit-data: ' . print_r($addData, TRUE);
        }
        // file_put_contents($this->logFolder . $this->logFilename, $loggedMsg . PHP_EOL, FILE_APPEND);
        file_put_contents($this->getDocumentRoot() . DIRECTORY_SEPARATOR . $this->logFolder . $this->logFilename, $loggedMsg . PHP_EOL, FILE_APPEND);
    }

    protected function getDocumentRoot()
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        if (Formatter::endsWith($documentRoot, DIRECTORY_SEPARATOR)) {
            $documentRoot = substr($documentRoot, 0, - 1);
        }
        return $documentRoot;
    }

    protected function checkSettings($forceRefresh = TRUE)
    {
        if (! $forceRefresh && $this->settingsCheckedOK) {
            return TRUE;
        }
        $this->settingsCheckedOK = FALSE;
        if (empty($this->logFilename)) {
            return FALSE;
        }
        $this->settingsCheckedOK = TRUE;
        return TRUE;
    }

    public function createLogFilename($filename, $useFileDateSuffix = TRUE)
    {
        $newFilename = $filenameConv = iconv('utf-8', 'ASCII//IGNORE//TRANSLIT', $filename);
        $pinfo = pathinfo($filenameConv);
        if ($useFileDateSuffix) {
            $newFilename = $pinfo['filename'] . '_' . date('Ymd') . (! empty($pinfo['extension']) ? '.' . $pinfo['extension'] : '');
        }
        return $newFilename;
    }

    /**
     * ================= setters and getters ====================
     */
    
    /**
     *
     * @param string $folder            
     * @param boolean $absolute            
     */
    public function setLogFolder($folder = NULL, $absolute = FALSE)
    {
        $this->logFolder = ($absolute ? $folder : (self::BASE_LOG_FOLDER . $folder));
        if (empty($this->logFolder)) {
            $this->logFolder = self::BASE_LOG_FOLDER;
        }
        if (substr($this->logFolder, - 1, 1) != '/') {
            $this->logFolder .= '/';
        }
    }

    public function setLogFilename($filename, $useFileDateSuffix = TRUE)
    {
        if (empty($filename)) {
            throw new Exception('Filename cannot be empty!');
        }
        $this->logFilename = $this->createLogFilename($filename, $useFileDateSuffix);
    }

    public function getLogFilename()
    {
        return $this->logFilename;
    }

    public function getLogFolder()
    {
        return $this->logFolder;
    }

    public static function getBaseLogFolder()
    {
        return self::BASE_LOG_FOLDER;
    }

    /**
     * ============= functions for log cleanup ===============
     */
    
    /**
     * -- THIS IS THE ONE TO CLEAN LOGS EVERY NOW AND THEN
     * cleans (truncates+removes old) all logs recursively from self::BASE_LOG_FOLDER
     */
    public static function cleanAndTruncateAllLogs()
    {
        self::initStatic('logger_clean_truncate', self::CLEAN_TRUNCATE_LOGFILE);
        self::logStatic('logger_clean_truncate', '===== Clear and truncate ALL Logs START =====');
        self::clearAndTruncateFolder(self::BASE_LOG_FOLDER, TRUE);
        self::logStatic('logger_clean_truncate', '===== Clear and truncate ALL Logs END =====' . PHP_EOL);
    }

    /**
     * checks if path secure for deleting log files (just for safety)
     *
     * @param string $folderPath            
     * @param boolean $allowDocumentRoot
     *            if FALSE, all paths above self::BASE_LOG_FOLDER will be considered unsafe
     * @return boolean
     */
    protected function isPathSecure($folderPath, $allowDocumentRoot = FALSE)
    {
        $folderAllowed = self::BASE_LOG_FOLDER;
        if (substr($folderAllowed, - 1) !== '/') {
            $folderAllowed .= '/';
        }
        
        if ($allowDocumentRoot) {
            $ups = explode('/', $folderAllowed);
            foreach ($ups as & $tmp) {
                $tmp = trim($tmp);
                if (! empty($tmp) && ! $tmp != '..' && $tmp .= '.') {
                    $folderAllowed .= '../';
                }
            }
        }
        // var_dump($folderAllowed, realpath($folderPath), realpath($folderAllowed));
        return stripos(realpath($folderPath), realpath($folderAllowed)) !== FALSE;
    }

    /**
     * clears old files and truncates too big ones within folder and (if $recursive is set to TRUE) recursively below
     *
     * @param string $folderPath            
     * @param boolean $recursive            
     * @param boolean $allowDocumentRoot
     *            for checking isPathSecure
     */
    public static function clearAndTruncateFolder($folderPath, $recursive = FALSE, $allowDocumentRoot = FALSE)
    {
        $inst = new self();
        if (substr($folderPath, - 1) !== '/') {
            $folderPath .= '/';
        }
        
        if (! $inst->isPathSecure($folderPath, $allowDocumentRoot)) {
            trigger_error('ERR: Path unsafe to be cleaned: ' . realpath($folderPath), E_USER_WARNING);
            return FALSE;
        }
        
        self::initStatic('logger_clean_truncate', self::CLEAN_TRUNCATE_LOGFILE);
        self::logStatic('logger_clean_truncate', 'Clear and truncate FOLDER (recursive: ' . ($recursive ? 'YES' : 'NO') . '): ' . $folderPath);
        $inst->clearAndTruncateLogFilesInFolder($folderPath);
        
        if ($recursive) {
            $dirhandle = opendir($folderPath);
            while ($dirname = readdir($dirhandle)) {
                if ($dirname == '.' || $dirname == '..') {
                    continue;
                }
                $dirPath = $folderPath . $dirname;
                if (is_dir($dirPath) && ! is_link($dirPath)) {
                    self::clearAndTruncateFolder($dirPath, $recursive);
                }
            }
        }
        
        closedir($dirhandle);
    }

    /**
     * clears old files and truncates too big ones (with some exceptions, @see @method isFileCleanable) within folder
     * this limits only to specified folder, so it can be any folder
     *
     * @param string $folderPath            
     */
    public static function clearAndTruncateLogFilesInFolder($folderPath)
    {
        if (substr($folderPath, - 1) !== '/') {
            $folderPath .= '/';
        }
        
        $extensions = self::getLogExtensionsToClear();
        $mask = $folderPath . '*.{' . implode(',', $extensions) . '}';
        
        self::initStatic('logger_clean_truncate', self::CLEAN_TRUNCATE_LOGFILE);
        self::logStatic('logger_clean_truncate', 'Clear and truncate FILES in folder: ' . $folderPath);
        
        $filesTruncated = 0;
        $filesDeleted = 0;
        foreach (glob($mask, GLOB_BRACE) as $filepath) {
            if (! is_file($filepath)) {
                continue;
            }
            
            if (! self::isFileCleanable($filepath)) {
                self::logStatic('logger_clean_truncate', 'Ommiting file - it\'s not cleanable: ' . $filepath);
                continue;
            }
            
            if (self::isFileOld($filepath)) {
                self::logStatic('logger_clean_truncate', 'Deleting old file: ' . $filepath);
                $filesDeleted ++;
                @unlink($filepath);
            } elseif (self::isFileTooBig($filepath)) {
                $newSize = round((self::FILE_MAX_SIZE > 0 ? self::FILE_MAX_SIZE * self::TRUNCATE_LOGSIZE_FACTOR : 1e6), 0);
                self::logStatic('logger_clean_truncate', 'Truncating too big file to ' . $newSize . ' bytes: ' . $filepath);
                $filesTruncated ++;
                //truncateFileBeggining($filepath, $newSize);
            }
        }
        
        if (($filesTruncated + $filesDeleted) > 0) {
            self::logStatic('logger_clean_truncate', "-- Summary for folder {$folderPath}: {$filesTruncated} truncated, {$filesDeleted} deleted");
        } else {
            self::logStatic('logger_clean_truncate', "-- Summary for folder {$folderPath}: no files affected");
        }
    }

    /**
     * lists file extensions considered log files
     *
     * @return array
     */
    protected static function getLogExtensionsToClear()
    {
        return array('txt','log');
    }

    /**
     * checks if file can be considered as too big (for truncating)
     *
     * @param string $filepath            
     * @return boolean
     */
    public static function isFileTooBig($filepath)
    {
        if (! file_exists($filepath)) {
            return FALSE;
        }
        return filesize($filepath) > self::FILE_MAX_SIZE;
    }

    /**
     * checks if file can be considered old (for removal)
     *
     * @param string $filepath            
     * @return boolean
     */
    public static function isFileOld($filepath)
    {
        if (! file_exists($filepath)) {
            return FALSE;
        }
        return (filemtime($filepath) + self::FILE_EXPIRATION_TIME) < time();
    }

    protected static function getUncleanableFiles()
    {
        return array();
    }

    /**
     *
     * @return array
     */
    public static function isFileCleanable($filepath)
    {
        $pinfo = pathinfo($filepath);
        
        if (in_array($pinfo['basename'], self::getUncleanableFiles())) {
            return FALSE;
        }
        
        return TRUE;
    }
}
?>
