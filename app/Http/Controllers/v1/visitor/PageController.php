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
use App\Models\Page;

class PageController extends Controller
{
    public function getAll(Request $request)
    {

        try {
            $perPage = $request->per_page??10;
            $page = $request->page??1;
            $ckey  = CommonCached::cache_find_page . "_" . $perPage . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = Page::getAll($perPage,  $page);
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
            $ckey  = CommonCached::cache_find_page_by_slug."_".$slug;
            $detail = CommonCached::getData($ckey);
            if (empty($detail)) {
                $detail = Page::getDetail($slug);
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
