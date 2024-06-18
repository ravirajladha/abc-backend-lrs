<?php


namespace App\Http\Helpers;

use Carbon\Carbon;


class DateTimeHelper
{
    const SYSTEM_DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const SYSTEM_DATE_FORMAT = 'Y-m-d';
    const CUSTOM_DATE_FORMAT = 'd M Y';
    const CUSTOM_DATE_TIME_FORMAT = 'd M Y H:i:s';

    public static function format($dateTime, $from = self::SYSTEM_DATE_FORMAT, $to = self::CUSTOM_DATE_FORMAT)
    {
        return Carbon::createFromFormat($from, $dateTime)->format($to);
    }

    public static function getNoOfDays($fromDate, $toDate)
    {
        return Carbon::parse($fromDate)->diffInDays($toDate);
    }

    public static function getLastLogin($timestamp)
    {
        $time = 0;
        $currentTimestamp = time();
        $timestamp = strtotime($timestamp);
        if (is_numeric($timestamp)) {
            $currentTimestamp = time();
            $timeDiff = $currentTimestamp - $timestamp;
        }

        if ($timeDiff < 60) {
            $time = 'Just now';
        } elseif ($timeDiff < 3600) {
            $minutes = floor($timeDiff / 60);
            $time = ($minutes > 1) ? "{$minutes} mins ago" : 'A minute ago';
        } elseif ($timeDiff < 86400) {
            $hours = floor($timeDiff / 3600);
            $time = ($hours > 1) ? "{$hours} hrs ago" : 'An hour ago';
        } elseif ($timeDiff < 604800) {
            $days = floor($timeDiff / 86400);
            $time = ($days > 1) ? "{$days} days ago" : 'Yesterday';
        } else {
            $time = date('d M Y', $timestamp); // For older dates, display in a standard format
        }
        return $time;
    }

    function formatTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        $formattedTime = '';

        if ($hours > 0) {
            $formattedTime .= $hours . ' Hr';
            if ($hours > 1) {
                $formattedTime .= 's';
            }
            $formattedTime .= ' ';
        }

        if ($minutes > 0) {
            $formattedTime .= $minutes . ' Min';
            if ($minutes > 1) {
                $formattedTime .= 's';
            }
            $formattedTime .= ' ';
        }

        if ($seconds > 0) {
            $formattedTime .= $seconds . ' Sec';
            if ($seconds > 1) {
                $formattedTime .= 's';
            }
            $formattedTime .= ' ';
        }

        return $formattedTime;
    }
}
