<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\RemoveData;
use App\Models\SurveyCategory;
use Illuminate\Http\Request;

class SurveyCategoryController extends Controller
{
    public function getListSurveyCategory( Request $request) {
        try {
            $perPage = $request->per_page ?? 100;
            $page = $request->current_page ?? 1;
            $ckey  = CommonCached::cache_find_survey_category . "_" . $perPage . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = SurveyCategory::getAll($perPage,  $page);
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


