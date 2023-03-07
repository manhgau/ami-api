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
use App\Models\ImplementationProcess;

class ImplementationProcessController extends Controller
{
    public function getImplementationProcess(Request $request)
    {

        try {
            $ckey  = CommonCached::cache_find_implementation_process;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = ImplementationProcess::getImplementationProcess();
                foreach ($datas as $key => $value) {
                    $value->youtube_id = GetYoutubeId::getYoutubeId($value->youtube_url);
                    $datas[$key] = $value;
                }
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
