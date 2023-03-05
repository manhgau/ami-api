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
use App\Models\QAndAItems;

class QAndAItemController extends Controller
{
    public function getAllQaItem(Request $request)
    {

        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $category_id = $request->category_id ?? 0;
            $ckey  = CommonCached::cache_find_qa_item_by_category_id . "_" . $perPage . "_" . $page . "_" . $category_id;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = QAndAItems::getAll($perPage,  $page, $category_id);
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
