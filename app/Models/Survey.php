<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'user_id',
        'color',
        'category_id',
        'description',
        'active',
        'state',
        'skip_count',
        'start_time',
        'end_time',
        'real_end_time',
        'question_layout',
        'progression_mode',
        'number_of_response_required',
        'point',
        'is_attempts_limited',
        'attempts_limit_min',
        'attempts_limit_max',
        'is_time_limited',
        'time_limit',
        'is_random_answer',
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

    public static  function getListSurvey($perPage = 10,  $page = 1, $user_id, $state = null)
    {
        $query =  self::where('deleted', self::NOT_DELETED)->where('active', self::ACTIVE)
            ->where('user_id', $user_id)->orderBy('created_at', 'DESC');
        if ($state != null) {
            $query->where('state', $state);
        }
        $query = $query->paginate($perPage, "*", "page", $page)->toArray();
        return $query;
    }

    public static  function getDetailSurvey($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('active', self::ACTIVE)->first();
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
