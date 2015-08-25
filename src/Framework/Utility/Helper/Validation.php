<?php
namespace Api\Framework\Utility\Helper;

class Validation
{
    public static function hasWhitespace($string)
    {
        return (bool) preg_match('/\s/', $string);
    }

    public static function isEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }
        
        // Next check the domain is real.
        $domain = explode("@", $email, 2);
        return checkdnsrr($domain[1]); // returns TRUE/FALSE;
    }

    public static function isNotEmpty($value)
    {
        if ($value == '' || $value == null) {
            return false;
        } else {
            return true;
        }
    }

    public static function isAlphabeticOnly($string, $include_space = true)
    {
        $regex = $include_space ? '/^[\p{L} ]+$/ui' : '/^[\p{L}]+$/ui';
        return (bool) preg_match($regex, $string);
    }

    public static function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp) && ($timestamp <= PHP_INT_MAX) && ($timestamp >= ~ PHP_INT_MAX);
    }

    public static function isDateEmpty($date)
    {
        return DateHelper::isEmpty($date);
    }

    public static function isFloat($value, $decimal = NULL)
    {
        $options = $decimal === NULL ? NULL : array('options' => array('decimal' => $decimal));
        if ($options === NULL) {
            return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
        } else {
            return filter_var($value, FILTER_VALIDATE_FLOAT, $options) !== false;
        }
    }

    public static function isInt($value, $min = NULL, $max = NULL)
    {
        $options = array('options' => array());
        if ($min !== NULL) {
            $options['options']['min_range'] = $min;
        }
        if ($max !== NULL) {
            $options['options']['max_range'] = $max;
        }
        if (empty($options['options'])) {
            return filter_var($value, FILTER_VALIDATE_INT) !== false;
        } else {
            return filter_var($value, FILTER_VALIDATE_INT, $options) !== false;
        }
    }

    public static function isBoolean($value)
    {
        $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        return $result !== NULL;
    }

    /**
     * Check if given string is proper URL (works better than filter_var)
     *
     * @author Krzysztof Kalkhoff
     *
     * @param string $url
     * @return bool
     */
    public static function isUrl($url)
    {
        return (bool) preg_match("_(^|[\s.:;?\-\]<\(])(https?:\/\/[-\w;\/?:@&=+$\|\_.!~*\|'()\[\]%#,☺]+[\w\/#](\(\))?)(?=$|[\s',\|\(\).:;?\-\[\]>\)])_i", $url);
    }
}
?>