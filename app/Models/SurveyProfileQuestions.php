<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyProfileQuestions extends Model
{

    protected $fillable = [
        'title',
        'survey_profile_id',
        'description',
        'question_type',
        'profile_type',
        'validation_required',
        'is_multiple',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'created_by', 'updated_by'];
    const ACTIVE = 1;
    const INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function updateSurveyProfileQuestions($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->update($data);
    }
    public static  function getSurveyQuestionProfile($survey_profile_id, $perPage, $page)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_profile_id', $survey_profile_id)->paginate($perPage, "*", "page", $page)->toArray();
    }
    public function scopeGetSurveyProfileQuestions()
    {
        return $this->where('deleted', self::NOT_DELETED);
    }

    public function surveyCategory()
    {
        return $this->belongsTo('App\Models\SurveyCategory', 'category_id', 'id');
    }

    public function userCreated()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }
}
