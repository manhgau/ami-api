<?php

namespace App\Helpers;

use App\Models\Package;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\UserPackage;
use Carbon\Carbon;

class CheckPackageUser
{
    public static function checkSurveykPackageUser($user_id)
    {
        $time_now = Carbon::now();
        $user_package = UserPackage::getPackageUser($user_id, $time_now);
        $count_survey = Survey::countSurvey($user_id);
        if ($user_package->limit_projects <= $count_survey) {
            return true;
        }
        return false;
    }

    public static function checkQuestionkPackageUser($user_id, $survey_id)
    {

        $time_now = Carbon::now();
        $user_package = UserPackage::getPackageUser($user_id, $time_now);
        $count_survey_question = SurveyQuestion::countQuestionOfSurvey($survey_id);
        if ($user_package->limit_questions <= $count_survey_question) {
            return true;
        }
        return false;
    }
}
