<?php

namespace App\Helpers;

class FormatDate
{
    public static function formatDate($data)
    {
        if (!$data) {
            return null;
        }
        $date = date_create($data);
        $data = date_format($date, 'Y-m-d H:i:s');
        return $data;
    }

    public static function formatDateStatistic($data)
    {
        if (!$data) {
            return null;
        }
        $date = date_create($data);
        $data = date_format($date, 'H:i,d/m/Y');
        return $data;
    }

    public static function formatDateStatisticNoTime($data)
    {
        if (!$data) {
            return null;
        }
        $date = date_create($data);
        $data = date_format($date, 'd/m/Y');
        return $data;
    }
}
