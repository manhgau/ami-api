<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Survey extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'user_id',
        'font_size',
        'background_id',
        'description',
        'active',
        'state',
        'skip_count',
        'question_count',
        'start_time',
        'end_time',
        'real_end_time',
        'question_layout',
        'progression_mode',
        'number_of_response',
        'point',
        'is_attempts_limited',
        'attempts_limit_min',
        'attempts_limit_max',
        'is_time_limited',
        'time_limit',
        'is_random_answer',
        'is_random',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted',
        'note',
    ];

    const ACTIVE = 1;
    const INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const STATUS_DRAFT = 'draft';
    const STATUS_ON_PROGRESS = 'on_progress';
    const STATUS_COMPLETED = 'completed';
    const  ANSWER_MULTIPLE = 0;
    const  ANSWER_SINGLE = 1;

    public static  function getListSurvey($perPage = 10,  $page = 1, $user_id, $state = null)
    {
        $query =  self::select(
            'id',
            'title',
            'user_id',
            'description',
            'state',
            'skip_count',
            'question_count',
            'start_time',
            'real_end_time',
            'number_of_response',
            'created_at',
            'updated_at',
        )
            ->where('deleted', self::NOT_DELETED)
            ->where('active', self::ACTIVE)
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'DESC');
        if ($state != null) {
            $query->where('state', $state);
        }
        $query = $query->paginate($perPage, "*", "page", $page)->toArray();
        return $query;
    }

    public static  function getDetailSurvey($survey_id)
    {

        return  DB::table('surveys  as a')
            ->leftJoin('images as b', 'b.id', '=', 'a.background_id')
            ->select(
                'a.*',
                'b.image as background',
            )
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.id', $survey_id)
            ->where('a.active', self::ACTIVE)
            ->first();
    }

    public static  function getDetailSurveyByUser($id, $user_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('user_id', $user_id)->where('active', self::ACTIVE)->first();
    }

    public static  function getSetupSurvey($survey_id)
    {
        return  DB::table('surveys  as a')
            ->leftJoin('images as b', 'b.id', '=', 'a.background_id')
            ->select(
                'a.title',
                'a.survey_profile_id',
                'a.font_size',
                'a.background_id',
                'a.is_answer_single',
                'a.is_random',
                'b.image as background',
            )
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.id', $survey_id)
            ->where('a.active', self::ACTIVE)
            ->first();
    }

    public static  function getDetailSurveyStatistic($id)
    {
        return self::select('title', 'description', 'view')->where('deleted', self::NOT_DELETED)->where('id', $id)->where('active', self::ACTIVE)->first();
    }

    public static  function updateSurvey($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('active', self::ACTIVE)->where('id', $id)->update($data);
    }

    public static  function countSurvey($user_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('active', self::ACTIVE)->where('user_id', $user_id)->count();
    }
}
