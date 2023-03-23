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
        if ($count_survey >= $user_package['limit_projects']) {
            return true;
        }
        return false;
    }

    public static function checkQuestionkPackageUser($user_id, $survey_id, $number = 0)
    {

        $time_now = Carbon::now();
        $user_package = UserPackage::getPackageUser($user_id, $time_now);
        $count_survey_question = SurveyQuestion::countQuestionOfSurvey($survey_id);
        if (($count_survey_question + $number) >= $user_package['limit_questions']) {
            return true;
        }
        return false;
    }
}
