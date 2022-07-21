<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 21/07/2022
 * Time: 13:44
 */

namespace App\Helpers\Common;

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
}