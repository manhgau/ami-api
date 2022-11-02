<?php

namespace App\Helpers;

class FormatDate
{
    public static function formatDate($data)
    {
        $data = date_format(date_create($data), 'Y-m-d h:i:s');
        return $data;
    }
}
