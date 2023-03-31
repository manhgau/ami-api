<?php

namespace App\Models;

/*use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;*/

class FormatDateType
{

    const FORMAT_D_M_Y                           = 'd/m/Y';
    const FORMAT_M_D_Y                          = 'm/d/Y';


    public static function getFormatDateType()
    {
        return [
            ['format_type' => self::FORMAT_D_M_Y, 'name' => 'DD/MM/YYYY'],
            ['format_type' => self::FORMAT_M_D_Y, 'name' => 'MM/DD/YYYY'],
        ];
    }
}
