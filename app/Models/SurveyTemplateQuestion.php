<?php

namespace App\Models;

use App\Helpers\Utility;
use Illuminate\Database\Eloquent\Model;

class SurveyTemplateQuestion extends Model
{



    public static  function getSurveyTemplateQuestion( $survey_template_id)
    {
        return self::where('survey_template_id', $survey_template_id)->get();
    }
}
