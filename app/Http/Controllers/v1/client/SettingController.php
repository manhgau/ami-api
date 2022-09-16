<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getInfo()
    {
        $ckey  = CommonCached::api_get_info;
        $datas = CommonCached::getData($ckey);
        if (empty($datas)) {
            $info = AppSetting::getInfo();
            if (!$info) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $datas = [];
            foreach ($info as $key => $item) {
                $datas[$item->key] = $item->value;
            }
            CommonCached::storeData($ckey, $datas);
        }
        return ClientResponse::responseSuccess('OK', $datas);
    }
}
