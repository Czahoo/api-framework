<?php
namespace Api\Framework\Utility\Helper;

class DateHelper
{
    /**
     * Returns current date
     *
     * @param bool $withTime
     * @return string
     */
    public static function nowDate($withTime = true)
    {
        return $withTime ? date('Y-m-d H:i:s') : date('Y-m-d');
    }

    public static function isEmpty($date)
    {
        return $date == '0000-00-00' || strtotime($date) <= 0;
    }

    public static function addDays($date, $days)
    {
        $curDate = new DateTime($date);
        $curDate->add(new DateInterval("P{$days}D"));
        return $curDate->format('Y-m-d');
    }

    public static function addHours($date, $hours)
    {
        $curDate = new DateTime($date);
        $curDate->add(new DateInterval("PT{$hours}H"));
        return $curDate->format('Y-m-d H:i:s');
    }

    public static function addMinutes($date, $minutes)
    {
        $curDate = new DateTime($date);
        $curDate->add(new DateInterval("PT{$minutes}M"));
        return $curDate->format('Y-m-d H:i:s');
    }
}
?>