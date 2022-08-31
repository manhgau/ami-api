<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\RemoveData;
use App\Models\SurveyTemplate;
use Illuminate\Http\Request;

class SurveyTemplateController extends Controller
{


    public function getListSurveyTemplate( Request $request) {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $category_id = $request->category_id??null;
            $ckey  = CommonCached::cache_find_survey_template . "_" . $category_id."_" . $perPage . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = SurveyTemplate::getListSurveyTemplate($perPage,  $page, $category_id);
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

    public function getDetailSurveyTemplate( Request $request) {
        try {
            $survey_template_id = $request->survey_template_id ?? 0;
            $ckey  = CommonCached::cache_find_survey_template_by_id . "_" . $survey_template_id;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = SurveyTemplate::getDetailSurveyTemplate($survey_template_id);
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


