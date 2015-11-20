<?php
namespace Api\Framework\Utility\Helper;

use Api\Framework\Basic\Object\Server;

class Formatter
{
    const STRING_ENDING = "...";

    const STRING_ENCODING = "utf-8";

    const HTML_TAG_START = "<";

    const HTML_TAG_END = ">";

    const HTML_ENDING = "/";

    const HTML_STYLE_ATTRIBUTE = "style";

    /**
     * Translates data using several conditions
     * @author Krzysztof Kalkhoff
     *
     * @param array $dictionary Array containing mapping from old key to new key
     * @param boolean $initWithData If return data will be initated with $data value
     * @param string $removeOldKey If after successful mapping old key should be removed from returned array
     * @return array
     */
    public static function translateData($data, $dictionary, $initWithData = true, $removeOldKey = true)
    {
        $return = $initWithData ? $data : [];
        foreach ($dictionary as $oldKey => $newKey) {
            if (isset($data[$oldKey])) {
                $return[$newKey] = $data[$oldKey];
                if($removeOldKey && isset($return[$oldKey])) {
                    unset($return[$oldKey]);
                }
            }
        }
        return $return;
    }
    
    /**
     * Make number float
     *
     * @param number $number
     */
    public static function makeFloat($number)
    {
        return floatval(str_replace(",", ".", $number));
    }

    /**
     * Make number decimal
     *
     * @param number $number
     * @param int $decimals
     * @return float
     */
    public static function makeDecimal($number, $decimals = 0)
    {
        return number_format(self::makeFloat($number), $decimals, '.', '');
    }

    public static function cutString($string, $length)
    {
        return substr($string, 0, $length) . (strlen($string) > $length ? self::STRING_ENDING : "");
    }

    public static function smartCutString($string, $length, $linkAddress = NULL)
    {
        $len = mb_strlen($string, self::STRING_ENCODING);
        if ($len <= $length) {
            return $string;
        }
        $pos = strpos($string, ' ', $length);
        $string = mb_substr($string, 0, $pos, self::STRING_ENCODING);
        
        for ($i = $pos - 1; $i > 0; $i --) {
            $chr = mb_substr($string, $i, 1, self::STRING_ENCODING);
            if ($chr == self::HTML_TAG_END) {
                break;
            } else {
                if ($chr == self::HTML_TAG_START) {
                    $string = mb_substr($string, 0, $i, self::STRING_ENCODING);
                }
            }
            if ($len > $pos) {
                $string .= empty($linkAddress) ? self::STRING_ENDING : makeHtmlLink($linkAddress, self::STRING_ENDING);
            }
            
            // array for missing HTML tags
            $tags = [];
            
            if (($i = mb_strpos($string, self::HTML_TAG_START)) === FALSE) {
                return $string;
            }
            
            while ($i >= 0 && $i < mb_strlen($string) && $i !== FALSE) {
                if (($j = mb_strpos($string, self::HTML_TAG_END, $i)) === FALSE) {
                    break;
                }
                $k = mb_strpos($string, ' ', $i);
                if ($k > $i && $k < $j) {
                    $tag = mb_substr($string, $i + 1, $k - $i - 1);
                } else {
                    $tag = mb_substr($string, $i + 1, $j - $i - 1);
                }
                $tag = strtolower($tag);
                
                if ((mb_strpos($tag, self::HTML_ENDING)) === 0) {
                    
                    $tag = mb_substr($tag, 1);
                    
                    if ($tags[count($tags) - 1] == $tag) {
                        unset($tags[count($tags) - 1]);
                    }
                } else {
                    $tags[count($tags)] = $tag;
                }
                
                $i = mb_strpos($string, self::HTML_TAG_START, $j);
            }
            
            // Add closing tags
            if (count($tags) > 0) {
                for ($i = count($tags) - 1; $i >= 0; $i --) {
                    $string .= self::HTML_TAG_START . self::HTML_ENDING . $tags[$i] . self::HTML_TAG_END;
                }
            }
            return $string;
        }
    }

    /**
     * Normalize string to English alphabet removing all Polish signs
     *
     * @author Krzysztof Kalkhoff
     *
     * @param string $msg
     * @return string
     */
    public static function textId($msg)
    {
        $return = "";
        $msg = mb_strtolower(mb_convert_encoding($msg, self::STRING_ENCODING), self::STRING_ENCODING);
        // $trans = array("ń" => "n", "ę" => "e", "ó" => "o", "ą" => "a", "ś" => "s", "ł" => "l", "ż" => "z", "ź" => "z", "ć" => "c");
        // $msg = strtr($msg, $trans);
        
        $len = mb_strlen($msg, self::STRING_ENCODING);
        for ($i = 0; $i < $len; $i ++) {
            $chr = mb_substr($msg, $i, 1, self::STRING_ENCODING);
            if ((ord($chr) >= 65 && ord($chr) <= 90) || (ord($chr) >= 97 && ord($chr) <= 122) || (ord($chr) >= 48 && ord($chr) <= 57)) {
                $return .= $chr;
            } else {
                if ($chr == " " || $chr == "_") {
                    $return .= "_";
                } elseif ($chr == "-") {
                    $return .= $chr;
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
    public static function readable($text)
    {
        return ucfirst(str_replace(['-', '_'], ' ', $text));
    }

    /**
     * Make given string a proper URL
     *
     * @author Krzysztof Kalkhoff
     *
     * @param string $url
     * @return string
     */
    public static function makeUrl($url)
    {
        if (! preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = 'http' . (isSecure() ? 's' : '') . "://" . $url;
        }
        return $url;
    }

    /**
     * Encode html tags with entities
     *
     * @author Krzysztof Kalkhoff
     *
     * @param array|string $data
     */
    public static function cleanHTMLData(&$data)
    {
        if (is_array($data)) {
            foreach ($data as $i => $w)
                if (is_array($w))
                    self::cleanHTMLData($data[$i]);
                else
                    $data[$i] = htmlentities($w, ENT_QUOTES, self::STRING_ENCODING);
        } else {
            $data = htmlentities($data, ENT_QUOTES, self::STRING_ENCODING);
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
    public static function restoreHTMLData(&$data, $allowedTags = null)
    {
        if (is_array($data)) {
            foreach ($data as $i => $w)
                if (is_array($w)) {
                    cleanHTMLData($data[$i]);
                } else {
                    $data[$i] = html_entity_decode($w, ENT_QUOTES, self::STRING_ENCODING);
                    if (! empty($allowedTags)) {
                        $data[$i] = strip_tags($data[$i], $allowedTags);
                    }
                }
        } else {
            $data = html_entity_decode($data, ENT_QUOTES, self::STRING_ENCODING);
            if (! empty($allowedTags)) {
                $data = strip_tags($data, $allowedTags);
            }
        }
    }

    /**
     * Check if string starts with a given pharase
     *
     * @param string $haystack
     *            String to check
     * @param string $needle
     *            Phrase it starts with
     * @param bool $caseSensitive
     * @return boolean
     */
    public static function startsWith($haystack, $needle, $caseSensitive = false)
    {
        $length = strlen($needle);
        return ($caseSensitive ? (substr($haystack, 0, $length) === $needle) : (mb_strtolower(substr($haystack, 0, $length)) === mb_strtolower($needle)));
    }

    /**
     * Check if string ends with a given pharase
     *
     * @param string $haystack
     *            String to check
     * @param string $needle
     *            Phrase it ends with
     * @param bool $caseSensitive
     * @return boolean
     */
    public static function endsWith($haystack, $needle, $caseSensitive = false)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        
        return ($caseSensitive ? (substr($haystack, - $length) === $needle) : (mb_strtolower(substr($haystack, - $length)) === mb_strtolower($needle)));
    }

    /**
     * Remove trailing slash or directory separator from given string
     *
     * @param string $string
     * @return string
     */
    public static function removeTrailingSlash($string)
    {
        $return = $string;
        if (self::startsWith($return, "/") || self::startsWith($return, DIRECTORY_SEPARATOR)) {
            $return = ($tmp = substr($return, 1)) ? $tmp : '';
        }
        return $return;
    }
    
    /**
     * Remove ending slash or directory separator from given string
     *
     * @param string $string
     * @return string
     */
    public static function removeLeadingSlash($string)
    {
        $return = $string;
        if (self::endsWith($return, "/") || self::endsWith($return, DIRECTORY_SEPARATOR)) {
            $return = ($tmp = substr($return, 0, -1)) ? $tmp : '';
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
    public static function makeAbsoluteLink($href)
    {
        return Server::getAbsoluteURL() . BASEPATH . self::removeTrailingSlash($href);
    }

    /**
     * Create relative link
     *
     * @author Krzysztof Kalkhoff
     *
     * @param string $href
     * @return string
     */
    public static function makeLink($href)
    {
        return BASEPATH . self::removeTrailingSlash($href);
    }

    /**
     * Clean data using strip_tags and trim
     *
     * @param array|string $data
     * @param array $filter
     * @return array
     */
    public static function cleanData($data, $filter = [])
    {
        $return = $data;
        if (is_array($return)) {
            foreach ($return as $i => &$w) {
                if (is_array($w)) {
                    $w = self::cleanData($return[$i]);
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
     * Create HTML attributes from given array.<br>
     * This function doesn't check if given attribute names are correct.
     *
     * @param array $array
     * @return string
     */
    public static function htmlAttributes($array)
    {
        $return = '';
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_bool($value)) {
                    if ($value === true) {
                        $return .= ' ' . $key;
                    }
                } elseif ($key == self::HTML_STYLE_ATTRIBUTE && is_array($value)) {
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
    public static function trimData(&$data)
    {
        if (is_array($data)) {
            foreach ($data as $i => $w)
                if (is_array($w)) {
                    self::trimData($data[$i]);
                } else {
                    $data[$i] = trim($w);
                }
        } else {
            $data = trim($data);
        }
    }

    /**
     * Cut data to field names specified in argument
     *
     * @author Krzysztof Kalkhoff
     *
     * @param array $data
     * @param array $possibleFields
     */
    public static function trimToPossibleFields($data, $possibleFields)
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
     * Make given text inline, remove every newline char
     *
     * @param string $content
     * @return string
     */
    public static function inline($text)
    {
        // Strip newline characters.
        $text = str_replace(chr(10), " ", $text);
        $text = str_replace(chr(13), " ", $text);
        // Replace single quotes.
        $text = str_replace(chr(145), chr(39), $text);
        $text = str_replace(chr(146), chr(39), $text);
        
        return $text;
    }
}