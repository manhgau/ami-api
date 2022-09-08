<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\FtpSv;
use App\Helpers\RemoveData;
use App\Models\AppSetting;
use App\Models\SurveyTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class SurveyTemplateController extends Controller
{


    public function getListSurveyTemplate(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $category_id = $request->category_id ?? null;
            $ckey  = CommonCached::cache_find_survey_template . "_" . $category_id . "_" . $perPage . "_" . $page;
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

    public function getDetailSurveyTemplate(Request $request)
    {
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

    public function updateLogoTemplate(Request $request)
    {
        try {
            $survey_template = SurveyTemplate::getDetailSurveyTemplate($request->template_id);
            if (!$survey_template) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            if ($file = $request->file('logo')) {
                $name =   md5($file->getClientOriginalName() . rand(1, 9999)) . '.' . $file->extension();
                $path = env('FTP_PATH') . "uploads/survey/logo";
                $result = FtpSv::upload($file, $name, $path, $request->template_id);
                $all_settings = AppSetting::getAllSetting();
                //setting
                $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
                if (!$result) {
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
                return ClientResponse::responseSuccess('OK', $image_domain .  $result);
            }
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
