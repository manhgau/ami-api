<?php

namespace App\Http\Middleware;

namespace App\Http\Middleware;

use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\AppSetting;
use App\Models\Images;
use App\Models\Survey;
use Closure;

class ClientAuthOwnerSurvey
{
    public function handle($request, Closure $next)
    {
        $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
        $id = $request->id ?? ($request->survey_id ?? '');
        $survey = Survey::getDetailSurvey($request->survey_id);
        if ($survey->state == Survey::STATUS_COMPLETED) {
            $all_settings = AppSetting::getAllSetting();
            $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
            $logo  = AppSetting::getByKey(AppSetting::LOGO, $all_settings);
            if ($survey->background_id) {
                $survey->background = $image_domain . Images::getDetailImage($survey->background_id)->image;
            }
            $survey->logo ? $survey->logo_default = 0 : $survey->logo_default = 1;
            $survey->logo ? $survey->logo = $image_domain . $survey->logo : $survey->logo = $image_domain . $logo;
            return ClientResponse::responseError('Bản ghi đã hoàn thành, không được sửa bản ghi này',  $survey);
        }
        if (!$survey) {
            return ClientResponse::responseError('Không có bản ghi phù hợp');
        }
        $survey_user = Survey::getDetailSurveyByUser($id, $user_id);
        if (!$survey_user) {
            return ClientResponse::response(ClientResponse::$client_auth_owner_survey, 'Dự án không phải của bạn');
        }
        return $next($request);
    }
}
