<?php

namespace App\Helpers;

use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\UserPackage;
use Carbon\Carbon;

class UpdateStateSurvey
{
    public static function updateStateSurvey()
    {
        $list_survey_expired = Survey::listSurveyTimeUp();
        $list_survey_expired_app = Survey::listSurveyTimeUpApp();
        $list_survey = Survey::listSurvey0nProgress();
        if ((is_array($list_survey_expired) && count($list_survey_expired) > 0) || (is_array($list_survey) && count($list_survey) > 0)) {
            foreach ($list_survey_expired as $survey) {
                $number_of_response_survey  = SurveyPartnerInput::countSurveyInput($survey['id'], SurveyPartnerInput::ANYNOMOUS_TRUE);
                if (($number_of_response_survey < $survey['limmit_of_response_anomyous']) & $survey['limmit_of_response_anomyous'] > 0) {
                    Survey::updateSurvey(["state" => Survey::STATUS_NOT_COMPLETED, 'status_not_completed' => Survey::TIME_UP], $survey['id']);
                } else {
                    Survey::updateSurvey(["state" => Survey::STATUS_COMPLETED], $survey['id']);
                }
            }
            foreach ($list_survey as $survey) {
                $number_of_response_user  = SurveyPartnerInput::countAllSurveyUserInput($survey['user_id']);
                $time_now = Carbon::now();
                $user_package = UserPackage::getPackageUser($survey['user_id'], $time_now)->toArray();
                if ($number_of_response_user >= $user_package['response_limit']) {
                    if ($survey['limmit_of_response_anomyous'] ==  0) {
                        Survey::updateSurvey(["state" => Survey::STATUS_COMPLETED], $survey['id']);
                    } else {
                        Survey::updateSurvey(["state" => Survey::STATUS_NOT_COMPLETED, 'status_not_completed' => Survey::LIMIT_EXPIRES], $survey['id']);
                    }
                }
            }
            return true;
        }
        return false;
    }
}
