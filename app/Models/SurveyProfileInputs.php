<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyProfileInputs extends Model
{

    protected $fillable = [
        'survey_profile_id',
        'id',
        'partner_id',
        'survey_id',
        'fullname',
        'year_of_birth',
        'gender',
        'province_code',
        'job_type_id',
        'academic_level_id',
        'marital_status_id',
        'personal_income_level_id',
        'family_income_level_id',
        'family_people',
        'has_children',
        'is_key_shopper',
        'created_at',
        'updated_at',
    ];




    public static  function getSurveyProfileInputDetail($survey_profile_id, $survey_id = null, $partner_id)
    {
        $query = self::where('survey_profile_id', $survey_profile_id)->where('partner_id', $partner_id);
        if ($survey_id != null) {
            $query->where('survey_id', $survey_id);
        }
        return $query->first();
    }
}
