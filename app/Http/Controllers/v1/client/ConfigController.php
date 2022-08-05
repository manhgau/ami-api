<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 06/07/2022
 * Time: 11:15
 */
namespace App\Http\Controllers\v1\client;

use Illuminate\Http\Request;
use App\Helpers\ClientResponse;
use Mail;
use App\Models\AppSetting;

class ConfigController extends Controller
{
    public function settings(Request $request){
        $msg = 'Basic setting for web client';
        $settings = new \stdClass();
        //
        $all_settings = AppSetting::getAllSetting();
        $settings->image_domain     = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
        $settings->is_maintain      = (int)AppSetting::getByKey(AppSetting::IS_MAINTAIN, $all_settings);
        //
        return ClientResponse::responseSuccess($msg, $settings);
    }

}