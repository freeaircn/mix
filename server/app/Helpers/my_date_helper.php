<?php
/**
 * Date Helpers
 */

if (!function_exists('my_is_timeout')) {
    /**
     * 超时
     *
     * @author freeair
     * @DateTime 2022-03-31
     * @param string $time
     * @param integer $threshold
     * @return void
     */
    function my_is_timeout(string $time = '', int $threshold = 300)
    {
        if (empty($time) || $threshold <= 0) {
            return true;
        }

        $now = date("Y-m-d H:i:s");
        if (strtotime($now) - strtotime($time) >= $threshold) {
            // 超时
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('my_any_day')) {
    /**
     * 输入日期时间字符串和前后天数，返回日期字符串
     *
     * @author freeair
     * @DateTime 2022-03-31
     * @param string $date
     * @param integer $offset
     * @return string
     */
    function my_any_day(string $date = '', int $offset = 0)
    {
        return date("Y-m-d", strtotime($offset . " days", strtotime($date)));
    }
}

if (!function_exists('my_prev_week')) {
    function my_prev_week(string $date = '')
    {
        return date('Y-m-d', strtotime($date . ' -1 week'));
    }
}

if (!function_exists('my_first_day_of_month')) {
    function my_first_day_of_month(string $date = '', int $offset = 0)
    {
        return date("Y-m-d", strtotime("first day of" . $offset . " month", strtotime($date)));
    }
}

if (!function_exists('my_last_day_of_month')) {
    function my_last_day_of_month(string $date = '', int $offset = 0)
    {
        return date("Y-m-d", strtotime("last day of" . $offset . " month", strtotime($date)));
    }
}

if (!function_exists('my_first_day_of_quarter')) {
    function my_first_day_of_quarter(string $date = '', int $offset = 0)
    {
        $year    = date('Y', strtotime($date));
        $quarter = ceil((date('n', strtotime($date))) / 3) + $offset;
        return date('Y-m-d', mktime(0, 0, 0, $quarter * 3 - 3 + 1, 1, $year));
    }
}

if (!function_exists('my_last_day_of_quarter')) {
    function my_last_day_of_quarter(string $date = '', $offset = 0)
    {
        $year    = date('Y', strtotime($date));
        $quarter = ceil((date('n', strtotime($date))) / 3) + $offset;
        return date('Y-m-d', mktime(23, 59, 59, $quarter * 3, date('t', mktime(0, 0, 0, $quarter * 3, 1, $year)), $year));
    }
}

if (!function_exists('my_last_day_of_year')) {
    function my_last_day_of_year(string $date = '', $offset = 0)
    {
        $year = date('Y', strtotime($date)) + $offset;
        return $year . '-12-31';
    }
}

if (!function_exists('my_first_day_of_year')) {
    function my_first_day_of_year(string $date = '', $offset = 0, $include_time = false)
    {
        $year = date('Y', strtotime($date)) + $offset;
        if ($include_time) {
            return date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $year));
        } else {
            return date('Y-m-d', mktime(0, 0, 0, 1, 1, $year));
        }
    }
}
