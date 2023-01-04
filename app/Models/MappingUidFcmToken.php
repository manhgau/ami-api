<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MappingUidFcmToken extends Model
{
    protected $fillable = [
        'partner_id',
        'fcm_token',
        'os',
        'device_id',
        'created_at',
        'updated_at',
        'status_fcm',
        'status',
    ];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    const OS_IOS = 'ios';
    const OS_ANDROID = 'android';
    const OS_WEBKIT = 'webkit';


    public static function getOSList()
    {
        return [
            self::OS_IOS,
            self::OS_ANDROID,
            self::OS_WEBKIT
        ];
    }

    public static  function getMappingUidFcmTokenByPartnerId($partner_id)
    {
        return self::where('status', self::STATUS_ACTIVE)->where('partner_id', $partner_id)->first();
    }

    public static  function checkFcmToken($partner_id, $device_id)
    {
        return self::where('status', self::STATUS_ACTIVE)->where('partner_id', $partner_id)->where('device_id', $device_id)->first();
    }

    public static  function getAll()
    {
        return self::where('status', self::STATUS_ACTIVE)->get();
    }
}
