<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SurveyQuestion extends Model
{

    protected $fillable = [
        'survey_id',
        'title',
        'description',
        'sequence',
        'question_type',
        'is_scored_question',
        'matrix_subtype',
        'is_time_limited',
        'time_limit',
        'comments_allowed',
        'comment_message',
        'comment_count_as_answer',
        'validation_required',
        'validation_length_min',
        'validation_length_max',
        'validation_min_float_value',
        'validation_min_date',
        'validation_max_date',
        'validation_error_message',
        'constr_mandatory',
        'constr_error_message',
        'is_conditional',
        'triggering_question_id',
        'triggering_answer_id',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted',
    ];


    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'updated_by', 'created_by'];

    public static  function createSurveyQuestion($data)
    {
        return SurveyQuestion::create($data);
    }

    public static  function getListSurveyQuestion($survey_id)
    {
        return self::select('id', 'title', 'description', 'is_page', 'page_id', 'sequence', 'question_type')->where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->orderBy('sequence', 'ASC')->get();
    }

    public static  function getDetailSurveyQuestion($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->first();
    }

    public static  function updateSurveyQuestion($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->update($data);
    }

    public static function countQuestion($survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->count();
    }

    public static  function getSurveyQuestion($ids)
    {
        return self::where('deleted', self::NOT_DELETED)->whereIn('id', $ids)->get();
    }
    public static  function getQuestionOfSurvey($survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->orderBy('sequence', 'ASC')->get();
    }
}
