<?php
namespace Api\Framework\Utility\Helper;

/**
 * Pack of usefull functions for arrays
 *
 * @author Krzysztof Kalkhoff
 *
 */
class ArrayHelper
{
    /**
     * Recursively checks if all of array values match empty condition
     *
     * @param array $array
     * @return boolean
     */
    public static function isEmpty($array)
    {
        if (is_array($array)) {
            foreach ($array as $v) {
                if (! self::isEmpty($v)) {
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
    public static function evenValues($array)
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
    public static function oddValues($array)
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
    public static function isAssoc(Array $array)
    {
        return array_keys($array) !== range(0, count($arr) - 1);
    }
}