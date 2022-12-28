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
}
