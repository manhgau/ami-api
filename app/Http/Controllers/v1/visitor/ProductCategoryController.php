<?php

/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 06/07/2022
 * Time: 11:15
 */

namespace App\Http\Controllers\v1\visitor;


use Illuminate\Http\Request;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\RemoveData;
use App\Models\ProductCategorys;

class ProductCategoryController extends Controller
{
    public function getAllProductCategory(Request $request)
    {

        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $ckey  = CommonCached::cache_find_product_category . "_" . $perPage . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = ProductCategorys::getAllProductCategory($perPage,  $page);
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

    public function getDetailProductCategory($category_id)
    {
        try {
            $ckey  = CommonCached::cache_find_product_category_by_id . "_" . $category_id;
            $detail = CommonCached::getData($ckey);
            if (empty($detail)) {
                $detail = ProductCategorys::getDetailProductCategory($category_id);
                CommonCached::storeData($ckey, $detail);
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
