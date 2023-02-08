<?php

namespace App\Helpers;

use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\UserPackage;
use Carbon\Carbon;

class CheckResponseOfSurvey
{
    public static function checkAllResponseOfSurvey($user_id, $data)
    {
        $time_now = Carbon::now();
        $user_package = UserPackage::getPackageUser($user_id, $time_now);
        $sum_response_survey = SurveyPartnerInput::countAllSurveyUserInput($user_id);
        if ($data < ($user_package->response_limit - $sum_response_survey)) {
            return true;
        }
        return false;
    }

    public static function checkResponseSettingOfSurvey($user_id, $data)
    {
        $time_now = Carbon::now();
        $user_package = UserPackage::getPackageUser($user_id, $time_now);
        $sum_response_survey = Survey::sumLimitOfResponseSurvey($user_id);
        if ($data < ($user_package->response_limit - $sum_response_survey)) {
            return true;
        }
        return false;
    }
}
