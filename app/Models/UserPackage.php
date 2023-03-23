<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserPackage extends Model
{

    protected $fillable = [
        'user_id',
        'package_id',
        'start_time',
        'end_time',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static  function getPackageUser($user_id, $time_now)
    {

        $result =  DB::table('user_packages')
            ->join('packages', 'packages.id', '=', 'user_packages.package_id')
            ->select(
                'packages.name',
                'packages.id',
                'packages.response_limit',
                'packages.limit_projects',
                'packages.limit_questions',
                'packages.add_logo',
                'packages.data_storage',
                'packages.logic_jumps',
                'user_packages.start_time',
                'user_packages.end_time',
            )
            ->where('user_packages.user_id', $user_id)
            ->where('user_packages.end_time', '>', $time_now)
            ->where('user_packages.status', self::STATUS_ACTIVE)->first();
        if (!$result) {
            $result = Package::getPackageFree();
        }
        return json_decode(json_encode($result), true);
    }

    public static  function getAllPackageUser($time)
    {
        return  DB::table('user_packages')
            ->join('packages', 'packages.id', '=', 'user_packages.package_id')
            ->select(
                'packages.name',
                'packages.id',
                'packages.response_limit',
                'packages.limit_projects',
                'packages.limit_questions',
                'packages.add_logo',
                'packages.data_storage',
                'packages.logic_jumps',
                'user_packages.start_time',
                'user_packages.end_time',
                'user_packages.user_id',
            )
            ->where('user_packages.end_time', $time)
            ->where('user_packages.status', self::STATUS_ACTIVE)->get()->toArray();
    }

    public static  function updatePackage($data, $id)
    {
        return self::where('id', $id)->update($data);
    }
    public function userCreated()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    public function userPackage()
    {
        return $this->belongsTo('App\Models\UserClient', 'user_id', 'id');
    }

    public function Package()
    {
        return $this->belongsTo('App\Models\Package');
    }

    public function userUpdated()
    {
        return $this->belongsTo('App\Models\User', 'updated_by', 'id');
    }
}
