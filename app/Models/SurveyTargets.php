<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SurveyTargets extends Model
{
    protected $fillable = [
        'survey_id',
        'target_value',
        'target_type',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public static  function getSurveyTarget($survey_id, $target_type = null)
    {
        $query =  self::where('survey_id', $survey_id);
        if ($target_type != null) {
            $query->where('target_type', $target_type);
        }
        return $query;
    }

    public static  function getDetailTargetSurvey($survey_id, $target_survey_id)
    {
        return self::where('survey_id', $survey_id)->where('id', $target_survey_id)->first();
    }
}
