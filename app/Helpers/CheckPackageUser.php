<?php

namespace App\Helpers;

use App\Models\Package;
use App\Models\Survey;

class CheckPackageUser
{
    public static function checkSurveykPackageUser($user_id)
    {

        $user_package = Package::userPackage($user_id);
        $count_survey = Survey::countSurvey($user_id);
        if ($user_package->limit_projects < $count_survey) {
            return true;
        }
        return false;
    }

    public static function checkQuestionkPackageUser($user_id)
    {

        $user_package = Package::userPackage($user_id);
        $count_survey = Survey::countSurvey($user_id);
        if ($user_package->limit_questions < $count_survey) {
            return true;
        }
        return false;
    }
}
