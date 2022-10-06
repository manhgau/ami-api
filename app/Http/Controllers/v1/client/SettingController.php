<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Models\AppSetting;

class SettingController extends Controller
{
    public function getInfo()
    {
        $rs = [];
        $data = AppSetting::getAllSetting();
        foreach ($data as $key => $item) {
            $rs[$item->key] = $item->value;
        }
        return ClientResponse::responseSuccess('OK', $rs);
    }
}
