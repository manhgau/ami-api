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
use App\Helpers\Common\ConstValue;
use App\Models\AppSetting;

class ConfigController extends Controller
{
    public function settings(Request $request){
        $msg = 'Basic setting for partner app';
        $settings = new \stdClass();
        $all_settings = AppSetting::getAllSetting();
        //setting
        $settings->image_domain     = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
        //check maintain
        $is_maintain = (int)AppSetting::getByKey(AppSetting::IS_MAINTAIN, $all_settings);
        $settings->is_maintain = $is_maintain;
        if($is_maintain==AppSetting::IS_MAINTAIN_VALUE){
            return ClientResponse::response(ClientResponse::$app_is_maintain, "Hệ thống đang bảo trì, vui lòng quay lại sau.");
        }
        //

        //version
        $settings->newest_version = floatval(1.0);
        $is_force_update = 0;
        $is_update = 0;
        $review_app = 0;
        $version = $request->version;
        $arr_os = ConstValue::$arr_os;
        $os = request()->header('os', '');
        $platform = request()->header('platform', '');
        $version_now = isset($arr_os[$os]) ? $arr_os[$os] : 0;

        if ($version_now > 0 && version_compare($version_now, $version) > 0) {
            $msg = ConstValue::$message_update_version;
            $is_update = 0;
            $is_force_update = 1;
        }
        $settings->is_force_update = $is_force_update;
        $settings->is_update = $is_update;
        $settings->in_review = $review_app;
        //END version

        return ClientResponse::responseSuccess($msg, $settings);
    }
}