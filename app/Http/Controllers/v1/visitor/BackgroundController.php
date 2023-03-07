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
use App\Helpers\GetYoutubeId;
use App\Models\Backgrounds;

class BackgroundController extends Controller
{
    public function getBackground(Request $request)
    {

        try {
            $ckey  = CommonCached::cache_find_background;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = Backgrounds::getBackground();
                $datas->youtube_id = GetYoutubeId::getYoutubeId($datas->youtube_url);
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
