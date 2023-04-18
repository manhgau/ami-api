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
use App\Models\StaticPages;

class StaticPagesController extends Controller
{
    public function getStaticPagesBySlug(Request $request)
    {

        try {
            $slug = $request->slug;
            $datas = StaticPages::getStaticPagesBySlug($slug);
            $datas['created_time'] = date_format($datas['created_at'], 'd/m/Y');
            unset($datas['created_at']);
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
