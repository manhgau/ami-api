<?php

namespace App\Http\Controllers\v1\visitor;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\RemoveData;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyTemplate;
use App\Models\TemplateWithQaCategory;
use App\Models\TemplateWithSurveyCategory;
use Illuminate\Http\Request;


class TemplateController extends Controller
{


    public function getListTemplateByCategoryQa(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $category_qa = $request->category_qa ?? null;
            $ckey  = CommonCached::cache_find_survey_template_by_categoey_qa . "_" . $category_qa . "_" . $perPage . "_" . $page;
            // $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = TemplateWithQaCategory::getListTemplateByCategoryQa($perPage,  $page, $category_qa);
                foreach ($datas['data'] as $key => $value) {
                    $data = $value;
                    $data->topic = TemplateWithSurveyCategory::getTopicTemplate($value->survey_template_id);
                    $datas['data'][$key] = $data;
                }
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

    public function getDetailTemplate(Request $request)
    {
        try {
            $survey_template_id = $request->survey_template_id ?? 0;
            $ckey  = CommonCached::cache_find_survey_template_by_template_id . "_" . $survey_template_id;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = SurveyTemplate::getDetailSurveyTemplate($survey_template_id);
                if (isset($datas['survey_id'])) {
                    $datas['background'] = Survey::getDetailSurvey($datas['survey_id'])->background ?? null;
                    $datas['question'] = SurveyQuestion::getQuestionFirst($datas['survey_id']);
                }
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
