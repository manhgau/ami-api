<?php

namespace App\Http\Controllers\v1\visitor;


use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\RemoveData;
use App\Models\QAndA;
use Illuminate\Http\Request;

class QAndAController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $category_id = $request->category_id;
            $ckey  = CommonCached::cache_find_qa . "_" . $perPage . "_" . $page . "_" . $category_id;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = QAndA::getAll($perPage, $page, $category_id);
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

    public function getQAndARelate(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $slug = $request->slug;
            $detail = QAndA::getDetail($slug);
            if (!$detail) {
                return ClientResponse::responseSuccess('Không có bản ghi liên quan');
            }
            $category_id = $detail->category_id;
            $ckey  = CommonCached::cache_find_qa_relate . "_" . $perPage . "_" . $page . "_" . $category_id . "_" . $slug;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = QAndA::getQAndARelate($perPage, $page,  $category_id, $slug);
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

    public function getDetail($slug)
    {
        try {
            $ckey  = CommonCached::cache_find_qa_by_slug . "_" . $slug;
            $detail = CommonCached::getData($ckey);
            if (empty($detail)) {
                $detail = QAndA::getDetail($slug);
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
