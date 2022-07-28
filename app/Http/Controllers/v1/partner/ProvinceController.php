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
use App\Helpers\Common\CommonCached;
use App\Helpers\RemoveData;
use App\Models\Province;

class ProvinceController extends Controller
{
    public function getProvince(Request $request)
    {

        try {
            $perPage = $request->per_page ?? 100;
            $page = $request->page ?? 1;
            $name = $request->name;
            $ckey = CommonCached::api_list_province . "_" . $perPage . "_" . $name . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas =  Province::getProvince($perPage,  $page, $name);
                $datas = RemoveData::removeUnusedData($datas);
                CommonCached::storeData($ckey, $datas);
            }

            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
