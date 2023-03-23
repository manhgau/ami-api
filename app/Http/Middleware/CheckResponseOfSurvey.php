<?php

namespace App\Http\Middleware;

namespace App\Http\Middleware;

use App\Helpers\ClientResponse;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\UserPackage;
use Carbon\Carbon;
use Closure;

class CheckResponseOfSurvey
{
    public function handle($request, Closure $next)
    {
        $id = $request->id ?? ($request->survey_id ?? '');
        $survey_user = Survey::getDetailSurvey($id);
        if (!$survey_user) {
            return ClientResponse::responseError('Không có bản ghi phù hợp');
        }
        $time_now = Carbon::now();
        $user_package = UserPackage::getPackageUser($survey_user->user_id, $time_now);
        $number_of_response_user  = SurveyPartnerInput::countAllSurveyUserInput($survey_user->user_id);
        $number_of_response_survey  = SurveyPartnerInput::countSurveyInput($id, SurveyPartnerInput::ANYNOMOUS_TRUE);
        if ($survey_user->state != Survey::STATUS_ON_PROGRESS || ($user_package['response_limit'] > 0 && ($number_of_response_user >=  $user_package['response_limit'])) || (($survey_user->limmit_of_response_anomyous != 0) & ($number_of_response_survey >= $survey_user->limmit_of_response_anomyous))) {
            return ClientResponse::response(ClientResponse::$survey_enough_responses, 'Khảo sát đã thu thập đủ số phản hồi,hoặc đã đóng');
        }
        return $next($request);
    }
}
