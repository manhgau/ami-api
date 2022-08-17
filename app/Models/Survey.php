<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Survey extends Model
{
    protected $fillable = [
        'title',
        'user_id',
        'color',
        'category_id',
        'description',
        'active',
        'state',
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
    ];

    const ACTIVE = 1;
    const INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static  function getListSurvey($perPage = 10,  $page = 1, $user_id)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->where('active',self::ACTIVE)->where('user_id',$user_id)->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailSurvey( $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('active',self::ACTIVE)->first();
    }

    public static  function updateSurvey($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('active',self::ACTIVE)->where('id', $id)->update($data);
    }

    public static  function countSurvey($user_id)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->where('active',self::ACTIVE)->where('user_id',$user_id)->count();
    }

    public static  function CheckNumberOfSurvey($user_id)
    {
        $survey_user_number =  DB::table('user_packages')
        ->join('packages', 'packages.id', '=', 'user_packages.package_id')
        ->select('packages.limit_projects')
        ->where('user_packages.user_id', $user_id)
        ->where('user_packages.status', self::STATUS_ACTIVE)
        ->orderBy('packages.level', 'DESC')->first();
        $limit_projects = $survey_user_number->limit_projects;
        if(!$survey_user_number){
           $package_free =  Package::query()
            ->where('status', self::STATUS_ACTIVE)
            ->where('level', 0)
            ->first();
            $limit_projects = $package_free->limit_projects;
        }
        if(static::countSurvey($user_id) < $limit_projects){
            return true;
        }
        return false;

    }



}
