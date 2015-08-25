<?php
namespace Api\Framework\Utility\Helper;

class FileHelper {
    public static function makeDir($dir) {
        if (! is_dir($folder)) {
            $path = explode(DIRECTORY_SEPARATOR, $folder);
            $tmpPath = "";
            foreach ($path as $nr => $subFolder) {
                if (empty($subFolder)) {
                    $tmpPath .= DIRECTORY_SEPARATOR;
                }
        
                $tmpPath .= $subFolder . DIRECTORY_SEPARATOR;
                if (! is_dir($tmpPath)) {
                    mkdir($tmpPath);
                }
            }
        }
        return true;
    }
    
    public static function removeDir($dir) {
        if (! endsWith($dir, DIRECTORY_SEPARATOR)) {
            $dir .= DIRECTORY_SEPARATOR;
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
                fullrmdir($w);
            }
        }
        rmdir($dir);
        return true;
    }
}