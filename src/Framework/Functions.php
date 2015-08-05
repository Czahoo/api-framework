<?php

/**
 * Returns current date
 * @author Krzysztof Kalkhoff
 *
 * @param bool $withTime
 * @return string
 */
function nowDate($withTime = true)
{
    return $withTime ? date('Y-m-d H:i:s') : date('Y-m-d');
}

/**
 * If request using SSL
 *
 * @author Krzysztof Kalkhoff
 *        
 * @return boolean
 */
function isSecure()
{
    return (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
}

/**
 * Get absolute URL of page
 *
 * @author Krzysztof Kalkhoff
 *        
 * @return string
 */
function getAbsolutePageURL()
{
    $pageURL = 'http';
    
    if (isSecure())
        $pageURL .= "s";
    
    $pageURL .= "://";
    
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"];
    }
    return $pageURL;
}

/**
 * Get relative URL of page
 *
 * @author Krzysztof Kalkhoff
 *        
 * @return string
 */
function currentPageRelativeURL()
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
function currentPageURL()
{
    return getAbsolutePageURL() . currentPageRelativeURL();
}

/**
 * Recursively checks if all of array values match empty condition
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array $array            
 * @return boolean
 */
function array_empty($array)
{
    if (is_array($array)) {
        foreach ($array as $v) {
            if (! array_empty($v)) {
                return false;
            }
        }
    } elseif (! empty($array)) {
        return false;
    }
    
    return true;
}

/**
 * Get even values of array
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array $array            
 * @return array
 */
function array_even_values($array)
{
    $actualValues = array_values($array);
    $values = array();
    for ($i = 0; $i <= count($array) - 1; $i += 2) {
        $values[] = $actualValues[$i];
    }
    return $values;
}

/**
 * Get odd values of array
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array $array            
 * @return array
 */
function array_odd_values($array)
{
    $actualValues = array_values($array);
    $values = array();
    if (count($actualValues) > 1) {
        for ($i = 1; $i <= count($array) - 1; $i += 2) {
            $values[] = $actualValues[$i];
        }
    }
    return $values;
}

/**
 * Check if array is associative
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array $array            
 * @return boolean
 */
function array_is_assoc(Array $array)
{
    return array_keys($array) !== range(0, count($arr) - 1);
}

/**
 * Function used for handling situation than shouldn't happen
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $msg            
 */
function debug($msg)
{
    if (! SERVER_LIVE) {
        echo "Requested URL: " . currentPageURL() . '<br>';
        echo $msg . '<br>';
        echo '<pre>';
        print_r(debug_backtrace());
        echo '</pre>';
        exit();
    } else {
        exit('Error occured, please contact server administrator');
    }
}

/**
 * Check if string starts with a given pharase
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $haystack
 *            String to check
 * @param string $needle
 *            Phrase it starts with
 * @param bool $caseSensitive            
 * @return boolean
 */
function startsWith($haystack, $needle, $caseSensitive = false)
{
    $length = strlen($needle);
    return ($caseSensitive ? (substr($haystack, 0, $length) === $needle) : (mb_strtolower(substr($haystack, 0, $length)) === mb_strtolower($needle)));
}

/**
 * Check if string ends with a given pharase
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $haystack
 *            String to check
 * @param string $needle
 *            Phrase it ends with
 * @param bool $caseSensitive            
 * @return boolean
 */
function endsWith($haystack, $needle, $caseSensitive = false)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    
    return ($caseSensitive ? (substr($haystack, - $length) === $needle) : (mb_strtolower(substr($haystack, - $length)) === mb_strtolower($needle)));
}

/**
 * Recursively remove given directory
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $dir            
 * @return boolean
 */
function fullrmdir($dir)
{
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

/**
 * Recursively create given directory
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $dir            
 * @return boolean
 */
function fullmkdir($folder)
{
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

/**
 * Remove trailing slash from given path or url
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $string            
 * @return string
 */
function removeTrailingSlash($string)
{
    $return = $string;
    if (startsWith($return, "/")) {
        $return = ($tmp = substr($return, 1)) ? $tmp : '';
    }
    return $return;
}

/**
 * Create link using absolute path to site
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $href            
 * @return string
 */
function makeAbsoluteLink($href)
{
    return getAbsolutePageURL() . BASEPATH . removeTrailingSlash($href);
}

/**
 * Create relative link
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $href            
 * @return string
 */
function makeLink($href)
{
    return BASEPATH . removeTrailingSlash($href);
}

/**
 * Clean data using strip_tags and trim
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array|string $data            
 * @param array $filter            
 * @return array
 */
function cleanData($data, $filter = array())
{
    $return = $data;
    if (is_array($return)) {
        foreach ($return as $i => &$w) {
            if (is_array($w)) {
                $w = cleanData($return[$i]);
            } elseif (! array_key_exists($i, $filter)) {
                $w = trim(strip_tags($return[$i]));
            }
        }
    } else {
        $return = trim(strip_tags($return));
    }
    return $return;
}

/**
 * Encode html tags with entities
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array|string $data            
 */
function cleanHTMLData(&$data)
{
    if (is_array($data)) {
        foreach ($data as $i => $w)
            if (is_array($w))
                cleanHTMLData($data[$i]);
            else
                $data[$i] = htmlentities($w, ENT_QUOTES, "UTF-8");
    } else {
        $data = htmlentities($data, ENT_QUOTES, "UTF-8");
    }
}

/**
 * Decode html entities encoded by cleanHTMLData
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array|string $data            
 * @param string|null $allowedTags            
 */
function restoreHTMLData(&$data, $allowedTags = null)
{
    if (is_array($data)) {
        foreach ($data as $i => $w)
            if (is_array($w)) {
                cleanHTMLData($data[$i]);
            } else {
                $data[$i] = html_entity_decode($w, ENT_QUOTES, "UTF-8");
                if (! empty($allowedTags)) {
                    $data[$i] = strip_tags($data[$i], $allowedTags);
                }
            }
    } else {
        $data = html_entity_decode($data, ENT_QUOTES, "UTF-8");
        if (! empty($allowedTags)) {
            $data = strip_tags($data, $allowedTags);
        }
    }
}

/**
 * Create HTML attributes from given array.<br>
 * This function doesn't check if given attribute names are correct.
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array $array            
 * @return string
 */
function htmlAttributes($array)
{
    $return = '';
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if (is_bool($value)) {
                if ($value === true) {
                    $return .= ' ' . $key;
                }
            } elseif ($key == 'style' && is_array($value)) {
                $return .= ' ' . $key . '="';
                foreach ($value as $k => $v) {
                    $return .= $k . ':' . $v . '; ';
                }
                $return .= '"';
            } else {
                $return .= ' ' . $key . '="' . $value . '"';
            }
        }
    }
    return $return;
}

/**
 * Clean data using trim
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array $data            
 */
function trimData(&$data)
{
    if (is_array($data)) {
        foreach ($data as $i => $w)
            if (is_array($w)) {
                trimData($data[$i]);
            } else {
                $data[$i] = trim($w);
            }
    } else {
        $data = trim($data);
    }
}

//
/**
 * Cut data to field names specified in argument
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param array $data            
 * @param array $possibleFields            
 */
function trimToPossibleFields($data, $possibleFields)
{
    $return = $data;
    foreach ($return as $key => $value) {
        if (! in_array($key, $possibleFields)) {
            unset($return[$key]);
        }
    }
    return $return;
}

/**
 * Check if given string is proper URL (works better than filter_var)
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $url            
 * @return bool
 */
function isUrl($url)
{
    return (bool) preg_match("_(^|[\s.:;?\-\]<\(])(https?:\/\/[-\w;\/?:@&=+$\|\_.!~*\|'()\[\]%#,☺]+[\w\/#](\(\))?)(?=$|[\s',\|\(\).:;?\-\[\]>\)])_i", $url);
}

/**
 * Make given string a proper URL
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $url            
 * @return string
 */
function makeUrl($url)
{
    if (! preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = (isSecure() ? 'https' : 'http') . "://" . $url;
    }
    return $url;
}

// czyści nazwę pliku (można podać pełną ścieżkę do pliku) tak aby zawierała tylko [0-9] [a-z] [A-Z] oraz _-
/**
 * Cleans file name from any unwanted chars.<br>
 * As a result filename will contain chars matching [0-9a-zA-Z_-]
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $filename            
 * @return string
 */
function cleanFileName(&$filename)
{
    $path = pathinfo($filename);
    $len = strlen($path['filename']);
    $ret = "";
    for ($i = 0; $i < $len; $i ++) {
        $chr = $path['filename'][$i];
        if ((ord($chr) >= 65 && ord($chr) <= 90) || (ord($chr) >= 97 && ord($chr) <= 122) || (ord($chr) >= 48 && ord($chr) <= 57)) {
            $ret .= $chr;
        } else {
            if ($chr == " ") {
                $ret .= "_";
            } else {
                if ($chr == "-") {
                    $ret .= $chr;
                } else {
                    if ($chr == "_") {
                        $ret .= $chr;
                    }
                }
            }
        }
    }
    return $ret . ".{$path['extension']}";
}

/**
 * Make given text inline, remove every newline char
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $content            
 * @return string
 */
function inline($text)
{
    // Strip newline characters.
    $text = str_replace(chr(10), " ", $text);
    $text = str_replace(chr(13), " ", $text);
    // Replace single quotes.
    $text = str_replace(chr(145), chr(39), $text);
    $text = str_replace(chr(146), chr(39), $text);
    
    return $text;
}

/**
 * Normalize string to English alphabet removing all Polish signs
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $msg            
 * @return string
 */
function textId($msg)
{
    $return = "";
    $msg = mb_strtolower($msg, 'utf-8');
    $trans = array("ń" => "n","ę" => "e","ó" => "o","ą" => "a","ś" => "s","ł" => "l","ż" => "z","ź" => "z","ć" => "c");
    $msg = strtr($msg, $trans);
    $len = mb_strlen($msg, 'utf-8');
    for ($i = 0; $i < $len; $i ++) {
        $chr = mb_substr($msg, $i, 1, 'utf-8');
        if ((ord($chr) >= 65 && ord($chr) <= 90) || (ord($chr) >= 97 && ord($chr) <= 122) || (ord($chr) >= 48 && ord($chr) <= 57)) {
            $return .= $chr;
        } else {
            if ($chr == " ") {
                $return .= "_";
            } else {
                if ($chr == "-") {
                    $return .= $chr;
                } else {
                    if ($chr == "_") {
                        $return .= $chr;
                    }
                }
            }
        }
    }
    return $return;
}

/**
 * Make string readable.<br>
 * Replaces '-' with '_' and make first letter uppercase
 *
 * @author Krzysztof Kalkhoff
 *        
 * @param string $text            
 * @return string
 */
function readable($text)
{
    return ucfirst(str_replace(array('-','_'), ' ', $text));
}