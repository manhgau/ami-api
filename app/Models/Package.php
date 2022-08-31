<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Package extends Model
{
    protected $fillable = [
        'name',
        'limit_projects',
        'limit_questions',
        'response_limit',
        'logic_jumps',
        'advanced_setting',
        'data_storage',
        'url_qr_code',
        'remove_ami_logo',
        'adda_logo',
        'support',
        'priority',
        'standard',
        'gift_selection',
    ];

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status', 'updated_by', 'created_by'];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const FREE  = 0;

    public static  function getListPackage($perPage = 10,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->where('status',self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailPackage( $id)
    {

        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('status',self::STATUS_ACTIVE)->first();
    }

    public static  function checkTheUserPackage($user_id)
    {
        $survey_user_number =  DB::table('user_packages')
        ->join('packages', 'packages.id', '=', 'user_packages.package_id')
        ->select('packages.limit_projects', 'packages.limit_questions')
        ->where('user_packages.user_id', $user_id)
        ->where('user_packages.status', self::STATUS_ACTIVE)
        ->orderBy('packages.level', 'DESC')->first();
        if(!$survey_user_number){
           $package_free =  Package::query()
            ->where('status', self::STATUS_ACTIVE)
            ->where('level', Package::FREE)
            ->first();
            return $package_free;
        }
        return $survey_user_number;

    }


}
