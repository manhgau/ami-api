<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'is_date',
        'format_date_time',
        'type_ranking',
        'name_level_1',
        'name_level_2',
        'name_level_3',
        'is_page',
        'page_id',
        'is_multiple',
        'sort_alphabetically',
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
    const MULTIPLE = 1;
    const NOT_MULTIPLE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const NO_PAGE  = 0;
    const IS_PAGE  = 1;


    protected $hidden = ['deleted', 'created_at', 'updated_at', 'updated_by', 'created_by'];

    public static  function createSurveyQuestion($data)
    {
        return SurveyQuestion::create($data);
    }

    public static  function deleteAllSurveyQuestions($survey_id, $page_id = null)
    {
        $query =  self::where('survey_id', $survey_id);
        if ($page_id != null) {
            $query->where('page_id', $page_id);
        }
        return $query->delete();
    }


    public static  function getListSurveyQuestion($survey_id, $question_id = null)
    {
        $query =  self::select(
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
            'sort_alphabetically',
            'is_time',
            'format_date_time',
            'is_page',
            'page_id'
        )
            ->where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('page_id', self::NO_PAGE);
        if ($question_id != null) {

            $query->where('id', '<>', $question_id);
        }
        return $query->orderBy('sequence', 'asc')->get();
    }

    public static  function getAllSurveyQuestionLogic($survey_id, $sequence = null, $sequence_group = null)
    {
        $query =  self::select(
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
            'sort_alphabetically',
            'is_time',
            'format_date_time',
            'is_page',
            'page_id'
        )
            ->where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('page_id', self::NO_PAGE);
        if ($sequence != null) {
            $query = $query->where('sequence', '>', $sequence);
        }
        if ($sequence_group != null) {
            $query = $query->where('sequence', '>=', $sequence_group);
        }
        return $query->orderBy('sequence', 'asc')->get();
    }

    public static  function getListQuestionLogic($survey_id)
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
            'sort_alphabetically',
            'is_time',
            'format_date_time',
            'is_page',
            'page_id'
        )
            ->where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('question_type', '<>', QuestionType::GROUP)
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

    public static  function getAllQuestionGroup($survey_id, $page_id)
    {
        return self::where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('page_id', $page_id)
            ->orderBy('sequence', 'asc')
            ->get();
    }

    public static  function listGroupQuestions($survey_id, $page_id, $logic_comes = null)
    {
        $query =  DB::table('survey_questions  as a')
            ->leftJoin('images as b', 'b.id', '=', 'a.background_id')
            ->select(
                'a.id',
                'a.survey_id',
                'a.sequence',
                'a.title',
                'a.description',
                'a.question_type',
                'a.skip_count',
                'a.view',
                'a.type_ranking',
                'a.is_multiple',
                'a.validation_random',
                'a.validation_required',
                'a.is_time',
                'a.is_date',
                'a.format_date_time',
                'a.is_page',
                'a.page_id',
                'a.name_level_1',
                'a.name_level_2',
                'a.name_level_3',
                'a.background_id',
                'a.logic',
                'b.image as background',
            )
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.survey_id', $survey_id)
            ->where('a.page_id', $page_id);
        if ($logic_comes != null) {
            $query = $query->whereNotIn('a.id', $logic_comes);
        }
        return $query->orderBy('a.sequence', 'asc')
            ->get();
    }

    public static  function getQuestionFirst($survey_id)
    {
        $query = DB::table('survey_questions  as a')
            ->leftJoin('images as b', 'b.id', '=', 'a.background_id')
            ->select(
                'a.id',
                'a.survey_id',
                'a.sequence',
                'a.title',
                'a.description',
                'a.question_type',
                'a.skip_count',
                'a.view',
                'a.type_ranking',
                'a.is_multiple',
                'a.validation_random',
                'a.sort_alphabetically',
                'a.validation_required',
                'a.is_time',
                'a.is_date',
                'a.format_date_time',
                'a.is_page',
                'a.page_id',
                'a.name_level_1',
                'a.name_level_2',
                'a.name_level_3',
                'a.background_id',
                'a.logic',
                'b.image as background',
            )
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.survey_id', $survey_id)
            ->where('a.question_type', '<>', QuestionType::GROUP);
        return  $query = $query->orderBy('a.sequence', 'asc')->first();
    }

    public static  function getListQuestion($survey_id, $perPage, $page, $random, $logic_comes = null)
    {
        $query = DB::table('survey_questions  as a')
            //->leftJoin('images as b', 'b.id', '=', 'a.background_id')
            ->select(
                'a.id',
                'a.survey_id',
                'a.sequence',
                'a.title',
                'a.description',
                'a.question_type',
                'a.skip_count',
                'a.view',
                'a.type_ranking',
                'a.is_multiple',
                'a.validation_random',
                'a.validation_required',
                'a.is_time',
                'a.is_date',
                'a.format_date_time',
                'a.is_page',
                'a.page_id',
                'a.name_level_1',
                'a.name_level_2',
                'a.name_level_3',
                'a.background_id',
                'a.logic',
                //'b.image as background',
            )
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.survey_id', $survey_id)
            ->where('a.page_id', self::NO_PAGE);
        if ($logic_comes != null) {
            $query = $query->whereNotIn('a.id', $logic_comes);
        }
        if ($random == 1) {
            $query = $query->inRandomOrder();
        } else {
            $query = $query->orderBy('a.sequence', 'asc');
        }
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getQuestionByLogic($survey_id, $question_id)
    {
        return DB::table('survey_questions  as a')
            ->leftJoin('images as b', 'b.id', '=', 'a.background_id')
            ->select(
                'a.id',
                'a.survey_id',
                'a.sequence',
                'a.title',
                'a.description',
                'a.question_type',
                'a.skip_count',
                'a.view',
                'a.type_ranking',
                'a.is_multiple',
                'a.validation_random',
                'a.sort_alphabetically',
                'a.validation_required',
                'a.is_time',
                'a.is_date',
                'a.format_date_time',
                'a.is_page',
                'a.page_id',
                'a.name_level_1',
                'a.name_level_2',
                'a.name_level_3',
                'a.background_id',
                'a.logic',
                'b.image as background',
            )
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.survey_id', $survey_id)
            ->where('a.id', $question_id)
            ->first();
    }

    public static  function getDetailSurveyQuestion($id)
    {
        return  DB::table('survey_questions  as a')
            ->leftJoin('images as b', 'b.id', '=', 'a.background_id')
            ->select(
                'a.*',
                'b.image as background',
            )
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.id', $id)
            ->first();
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
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('is_page', self::IS_PAGE)->count();
    }

    public static function countQuestionOfSurvey($survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('is_page', self::NO_PAGE)->count();
    }

    public static function countSequence($survey_id, $page_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('page_id', $page_id)->count();
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

    public static  function getListQuestionExportFile($survey_id)
    {
        $query =  self::select(
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
            'is_date',
            'format_date_time',
            'is_page',
            'page_id'
        )
            ->where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('page_id', self::NO_PAGE);
        return $query->orderBy('sequence', 'asc')->get();
    }
}
