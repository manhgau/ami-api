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
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->where('status', self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailPackage($id)
    {

        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('status', self::STATUS_ACTIVE)->first();
    }

    public static  function getPackageFree()
    {

        return self::where('deleted', self::NOT_DELETED)->select(
            'name',
            'id',
            'response_limit',
            'level',
            'limit_projects',
            'limit_questions',
            'add_logo',
        )->where('status', self::STATUS_ACTIVE)->where('level', 0)->first();
    }


    public static  function userPackage($user_id)
    {
        $survey_user_number =  DB::table('user_packages as a')
            ->join('packages as b', 'b.id', '=', 'a.package_id')
            ->select('b.limit_projects', 'b.limit_questions')
            ->where('a.user_id', $user_id)
            ->where('a.status', self::STATUS_ACTIVE)
            ->orderBy('b.level', 'DESC')->first();
        if (!$survey_user_number) {
            $package_free =  Package::query()
                ->where('status', self::STATUS_ACTIVE)
                ->where('level', Package::FREE)
                ->first();
            return $package_free;
        }
        return $survey_user_number;
    }
}
