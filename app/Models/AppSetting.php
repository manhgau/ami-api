<?php

namespace App\Models;

use App\Helpers\Common\CommonCached;
use Illuminate\Database\Eloquent\Model;


class AppSetting extends Model
{
    const FRONTEND_WEB_URL                  = 'frontend_web_url';
    const IMAGE_DOMAIN                      = 'image_domain';
    const IS_MAINTAIN                       = 'is_maintain';

    protected $fillable = [
        'key',
        'value',
        'comment',
    ];

    public static function getAllSetting(){
        $key    = CommonCached::app_all_setting;
        $data   = CommonCached::getData($key);
        if(empty($data)){
            $data = AppSetting::all();
            CommonCached::storeData($key, $data, true);
        }
        return $data;
    }

    public static function getByKey($key, $all_settings = null){
        $val = '';
        if(!$all_settings) {
            $all_settings = self::getAllSetting();
        }
        if($all_settings){
            foreach ($all_settings as $v){
                if(strtolower($v->key)==strtolower($key)){
                    $val = $v->value;
                    break;
                }
            }
        }
        return $val;
    }
}
