<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestion extends Model
{

    protected $fillable = [
        'survey_id',
        'title',
        'background',
        'description',
        'sequence',
        'skip_count',
        'question_type',
        'is_scored_question',
        'matrix_subtype',
        'is_time',
        'type_ranking',
        'name_level_1',
        'name_level_2',
        'name_level_3',
        'is_page',
        'page_id',
        'is_multiple',
        'format_date_time',
        'validation_required',
        'validation_random',
        'validation_length_min',
        'validation_length_max',
        'validation_min_float_value',
        'validation_min_date',
        'validation_max_date',
        'validation_error_message',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted',
    ];


    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const LOGIC = 1;
    const UNLOGIC = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const NO_PAGE  = 0;

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'updated_by', 'created_by'];

    public static  function createSurveyQuestion($data)
    {
        return SurveyQuestion::create($data);
    }

    public static  function getListSurveyQuestion($survey_id)
    {
        return self::select('id', 'survey_id', 'title', 'is_page', 'page_id', 'sequence', 'question_type')
            ->where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('page_id', self::NO_PAGE)
            ->orderBy('sequence', 'asc')
            ->get();
    }

    public static  function getAllQuestion($survey_id, $page_id)
    {
        return self::select('id', 'sequence')
            ->where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('page_id', $page_id)
            ->orderBy('sequence', 'asc')
            ->get();
    }

    public static  function listGroupQuestions($survey_id, $page_id)
    {
        return self::select('id', 'survey_id', 'title', 'is_page', 'page_id', 'sequence', 'question_type')
            ->where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('page_id', $page_id)
            ->orderBy('sequence', 'asc')
            ->get();
    }

    public static  function getListQuestion($survey_id, $perPage, $page)
    {
        return self::select(
            'id',
            'survey_id',
            'sequence',
            'title',
            'description',
            'question_type',
            'skip_count',
            'view',
            'type_ranking',
            'is_multiple',
            'validation_random',
            'is_time',
            'format_date_time'
        )
            ->where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->orderBy('sequence', 'ASC')
            ->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailSurveyQuestion($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->first();
    }

    public static  function checkQuestionOfSurvey($survey_id, $question_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('id', $question_id)->first();
    }

    public static  function updateSurveyQuestion($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->update($data);
    }

    public static  function updateManySurveyQuestion($data, $survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->update($data);
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

    public static  function getNameLevelRanking($question_id)
    {
        $question_detail = self::getDetailSurveyQuestion($question_id);
        return [
            1                   => $question_detail->name_level_1,
            2                   => $question_detail->name_level_1,
            3                   => $question_detail->name_level_1,
            4                   => $question_detail->name_level_1,
            5                   => $question_detail->name_level_1,
            6                   => $question_detail->name_level_1,
            7                   => $question_detail->name_level_2,
            8                   => $question_detail->name_level_2,
            9                   => $question_detail->name_level_3,
            10                  => $question_detail->name_level_3,
        ];
    }
}
