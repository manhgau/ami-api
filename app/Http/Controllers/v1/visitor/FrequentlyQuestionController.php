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
use App\Models\FrequentlyQuestions;

class FrequentlyQuestionController extends Controller
{
    public function getFrequentlyQuestion(Request $request)
    {

        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $ckey  = CommonCached::cache_find_frequently_question . "_" . $perPage . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = FrequentlyQuestions::getFrequentlyQuestion($perPage,  $page);
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
