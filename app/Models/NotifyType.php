<?php

namespace App\Models;

/*use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;*/

class NotifyType
{

    const NOTIFICATION_COMMON                           = 'common';
    const NOTIFICATION_CONFIRM                          = 'confirm';
    const NOTIFICATION_WARNING                          = 'warning';
    const NOTIFICATION_OFTEN                            = 'often';


    public static function getNotficationType()
    {
        return [
            self::NOTIFICATION_COMMON,
            self::NOTIFICATION_CONFIRM,
            self::NOTIFICATION_WARNING,
            self::NOTIFICATION_OFTEN,
        ];
    }
}
