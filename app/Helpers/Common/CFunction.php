<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 21/07/2022
 * Time: 13:44
 */

namespace App\Helpers\Common;

use App\Models\AppSetting;
use Illuminate\Support\Str;


class CFunction{
    /**
     * @param $phone
     * @return bool
     */
    public static function isPhoneNumber($phone){
        if(is_numeric($phone) && (strlen($phone)==10) && (substr($phone,0,1)=='0') ){
            return true;
        }
        return false;
    }

    public static function getFrontendWebUrl(){
        return AppSetting::getByKey(AppSetting::FRONTEND_WEB_URL);
    }

    public static function generateUuid(){
        return (string) Str::uuid();
    }
}