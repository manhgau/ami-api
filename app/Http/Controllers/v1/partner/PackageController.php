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
use App\Models\Package;

class PackageController extends Controller
{
    public function getListPackage(Request $request)
    {
        try {
            $ckey  = CommonCached::cache_find_package;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $type = Package::getTypePackage();
                $datas = [];
                foreach ($type as $key => $value) {
                    $data = Package::getAllPackage($value);
                    if ($data) {
                        $datas[$key] = $data;
                    }
                }
                CommonCached::storeData($ckey, $datas, true);
            }
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getDetailPackage($id)
    {
        try {
            $ckey  = CommonCached::cache_find_package_by_id . "_" . $id;
            $detail = CommonCached::getData($ckey);
            if (empty($detail)) {
                $detail = Package::getDetailPackage($id);
                CommonCached::storeData($ckey, $detail, true);
            }
            if (!$detail) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
