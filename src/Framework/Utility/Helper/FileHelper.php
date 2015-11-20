<?php
namespace Api\Framework\Utility\Helper;

class FileHelper
{

    const DIR_SEPARATOR = "/";

    /**
     * Recursively create given directory
     *
     * @param string $dir            
     * @return boolean
     */
    public static function makeDir($dir)
    {
        $dir = str_replace(DIRECTORY_SEPARATOR, self::DIR_SEPARATOR, Formatter::removeLeadingSlash($dir));
        if (! is_dir($dir)) {
            $path = explode(self::DIR_SEPARATOR, $dir);
            $tmpPath = "";
            foreach ($path as $nr => $subFolder) {
                if (empty($subFolder)) {
                    $tmpPath .= self::DIR_SEPARATOR;
                }
                
                $tmpPath .= $subFolder . self::DIR_SEPARATOR;
                if (! is_dir($tmpPath)) {
                    mkdir($tmpPath);
                }
            }
        }
        return true;
    }

    /**
     * Recursively remove given directory
     *
     * @param string $dir            
     * @return boolean
     */
    public static function removeDir($dir)
    {
        if (! Formatter::endsWith($dir, DIRECTORY_SEPARATOR) && ! Formatter::endsWith($dir, self::DIR_SEPARATOR)) {
            $dir .= self::DIR_SEPARATOR;
        }
        
        if (! is_dir($dir)) {
            return false;
        }
        
        if ($handle = opendir($dir)) {
            $dirsToVisit = array();
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($dir . $file)) {
                        $dirsToVisit[] = $dir . $file;
                    } else 
                        if (is_file($dir . $file)) {
                            unlink($dir . $file);
                        }
                }
            }
            closedir($handle);
            foreach ($dirsToVisit as $i => $w) {
                self::removeDirectory($w);
            }
        }
        rmdir($dir);
        return true;
    }
}