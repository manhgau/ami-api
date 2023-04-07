<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'slug',
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
        'is_default'
    ];

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status', 'updated_by', 'created_by'];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const FREE  = 1;
    const MIEN_PHI  = 'mien-phi';
    const CO_BAN  = 'co-ban';

    public static  function getAllPackage($slug)
    {
        return self::select('name', 'description', 'slug', 'limit_projects', 'limit_questions', 'data_storage')->where('deleted', self::NOT_DELETED)->where('slug', $slug)->where('status', self::STATUS_ACTIVE)->first();
    }

    public static  function getDetailPackage($id)
    {

        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('status', self::STATUS_ACTIVE)->first();
    }
    public static function getTypePackage()
    {
        return [
            self::MIEN_PHI,
            self::CO_BAN,
        ];
    }

    public static  function getPackageFree()
    {

        return self::where('deleted', self::NOT_DELETED)->select(
            'is_default',
            'name',
            'slug',
            'id',
            'response_limit',
            'limit_projects',
            'limit_questions',
            'add_logo',
            'data_storage',
            'logic_jumps',
        )->where('status', self::STATUS_ACTIVE)->where('is_default', 1)->first();
    }


    public static  function userPackage($user_id)
    {
        $survey_user_number =  DB::table('user_packages as a')
            ->join('packages as b', 'b.id', '=', 'a.package_id')
            ->select('b.limit_projects', 'b.limit_questions')
            ->where('a.user_id', $user_id)
            ->where('a.status', self::STATUS_ACTIVE)
            ->first();
        if (!$survey_user_number) {
            $package_free =  Package::query()
                ->where('status', self::STATUS_ACTIVE)
                ->where('is_default', Package::FREE)
                ->first();
            return $package_free;
        }
        return $survey_user_number;
    }
}
