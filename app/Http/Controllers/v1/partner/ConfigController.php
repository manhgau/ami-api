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

class ConfigController extends Controller
{
    public function settings(Request $request){
        $msg = 'Basic setting for partner app';
        $settings = new \stdClass();

        //
        $settings->image_domain = env('IMAGE_DOMAIN');
        $settings->is_maintain = 0;

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

        return ClientResponse::responseSuccess($msg, $settings);
    }
}