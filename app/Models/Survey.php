<?php

namespace App\Models;

use Carbon\Carbon;
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
        'link_url',
        'title_color',
        'content_color',
        'button_color',
        'text_color_of_button',
        'background_id',
        'description',
        'is_ami',
        'active',
        'state',
        'state_ami',
        'status_not_completed',
        'skip_count',
        'question_count',
        'start_time',
        'end_time',
        'real_end_time',
        'question_layout',
        'progression_mode',
        'limmit_of_response',
        'limmit_of_response_anomyous',
        'number_of_response',
        'point',
        'is_answer_single',
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
    const STATUS_NOT_COMPLETED = 'not_completed';
    const STATUS_COMPLETED = 'completed';

    const TIME_UP = 'time_up';
    const LIMIT_EXPIRES = 'limit_expires';

    const URL = 'url';
    const AMI = 'ami';

    const  ANSWER_MULTIPLE = 0;
    const  ANSWER_SINGLE = 1;

    const  DATA_URL = 0;
    const  DATA_URL_AND_AMI = 1;

    public static  function getListSurvey($perPage = 10,  $page = 1, $user_id, $states = null)
    {
        $query =  self::select(
            'id',
            'title',
            'user_id',
            'description',
            'is_ami',
            'state',
            'state_ami',
            'status_not_completed',
            'skip_count',
            'question_count',
            'start_time',
            'real_end_time',
            'end_time',
            'limmit_of_response_anomyous',
            'limmit_of_response',
            'created_at',
            'updated_at',
        )
            ->where('deleted', self::NOT_DELETED)
            ->where('active', self::ACTIVE)
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'DESC');
        if ($states != null) {
            $query->whereIn('state', $states);
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

    public static  function countSurveyLinkUrlNotNull($user_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('user_id', $user_id)->where('active', self::ACTIVE)->whereNotNull('link_url')->count();
    }

    public static  function getSetupSurvey($survey_id)
    {
        return  DB::table('surveys  as a')
            ->leftJoin('images as b', 'b.id', '=', 'a.background_id')
            ->select(
                'a.title',
                'a.user_id',
                'a.description',
                'a.survey_profile_id',
                'a.font_size',
                'a.letter_font',
                'a.title_color',
                'a.content_color',
                'a.button_color',
                'a.text_color_of_button',
                'a.background_id',
                'a.is_answer_single',
                'a.is_random',
                'a.view',
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

    public static  function sumLimitOfResponseSurvey($user_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('active', self::ACTIVE)->where('user_id', $user_id)->sum('limmit_of_response');
    }

    public static  function listSurveyTimeUp()
    {
        return self::select('id', 'title', 'user_id', 'limmit_of_response',  'limmit_of_response_anomyous')
            ->where('deleted', self::NOT_DELETED)
            ->where('active', self::ACTIVE)
            ->where('real_end_time', '<', Carbon::now())
            ->where('state', self::STATUS_ON_PROGRESS)
            ->get()->toArray();
    }

    public static  function listSurvey0nProgress()
    {
        return self::select('id',  'title', 'user_id', 'limmit_of_response',  'limmit_of_response_anomyous')
            ->where('deleted', self::NOT_DELETED)
            ->where('active', self::ACTIVE)
            ->where('real_end_time', '>', Carbon::now())
            ->where('state', self::STATUS_ON_PROGRESS)
            ->get()->toArray();
    }

    public static  function listSurveyTimeUpApp()
    {
        return self::select('id', 'title', 'user_id', 'limmit_of_response', 'limmit_of_response_anomyous')
            ->where('deleted', self::NOT_DELETED)
            ->where('active', self::ACTIVE)
            ->where('end_time', '<', Carbon::now())
            ->where('state_ami', self::STATUS_ON_PROGRESS)
            ->get()->toArray();
    }

    public static  function getAllSurvey()
    {
        return self::select(
            'id',
            'title',
            'user_id',
            'start_time',
            'end_time',
            'real_end_time',
        )->where('deleted', self::NOT_DELETED)->get()->toArray();
    }
}
