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
use App\Models\StaticPages;

class StaticPagesController extends Controller
{
    public function getStaticPagesBySlug(Request $request)
    {

        try {
            $slug = $request->slug;
            $ckey  = CommonCached::cache_find_static_page_category_by_slug;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = StaticPages::getStaticPagesBySlug($slug);
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
