<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\RemoveData;
use App\Models\SurveyCategory;
use App\Models\TemplateWithSurveyCategory;
use Illuminate\Http\Request;


class SurveyTemplateController extends Controller
{


    public function getListSurveyTemplate(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $category_survey = $request->category_survey ?? null;
            $ckey  = CommonCached::cache_find_survey_template_by_categoey_survey . "_" . $category_survey . "_" . $perPage . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = TemplateWithSurveyCategory::getListTemplateByCategorySurvey($perPage,  $page, $category_survey);
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

    public function getTopicTemplate(Request $request)
    {
        try {
            $ckey  = CommonCached::cache_find_survey_template_topic;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = SurveyCategory::getALL();
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
