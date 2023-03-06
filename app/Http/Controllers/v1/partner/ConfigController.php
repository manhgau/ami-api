<?php

/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 06/07/2022
 * Time: 11:15
 */

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use App\Helpers\ClientResponse;
use App\Models\AppSetting;

class ConfigController extends Controller
{
    public function settings(Request $request)
    {
        $msg = 'Basic setting for partner app';
        $settings = new \stdClass();
        $all_settings = AppSetting::getAllSetting();
        //setting
        $settings->image_domain     = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
        //check maintain
        $is_maintain = (int)AppSetting::getByKey(AppSetting::IS_MAINTAIN, $all_settings);
        $settings->is_maintain = $is_maintain;
        if ($is_maintain == AppSetting::IS_MAINTAIN_VALUE) {
            return ClientResponse::response(ClientResponse::$app_is_maintain, "Hệ thống đang bảo trì, vui lòng quay lại sau.");
        }
        //info
        $rs = [];
        foreach ($all_settings as $key => $item) {
            $rs[$item->key] = $item->value;
        }
        //check fcmToken
        //version
        $newest_version = [
            'android_apps'  =>  '1.0.0',
            'ios_apps'  =>  '1.0.0',
        ];
        $force_update_version  = [
            'android_apps'  =>  '1.0.0',
            'ios_apps'  =>  '1.0.0',
        ];
        $settings->newest_version = $newest_version;
        $settings->force_update_version = $force_update_version;
        $settings->info = $rs;
        $settings->info['config_firebase_android'] = json_decode(AppSetting::getByKey(AppSetting::FIREBASE_ANDROID, $all_settings), true);
        $settings->info['config_firebase_ios'] = json_decode(AppSetting::getByKey(AppSetting::FIREBASE_IOS, $all_settings), true);
        //END version

        return ClientResponse::responseSuccess($msg, $settings);
    }
}
