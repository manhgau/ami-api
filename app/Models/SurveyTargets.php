<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class SurveyTargets extends Model
{
    protected $fillable = [
        'survey_id',
        'target_id',
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
}
