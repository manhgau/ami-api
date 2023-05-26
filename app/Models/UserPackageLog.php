<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserPackageLog extends Model
{

    protected $fillable = [
        'package_id',
        'start_time',
        'end_time',
        'user_id',
        'status',
        'log_type',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static  function getJobType($perPage = 10,  $page = 1, $user_id)
    {
        $query =  DB::table('user_package_logs as a')
            ->join('packages as b', 'b.id', '=', 'a.package_id')
            ->select(
                'a.id',
                'a.user_id',
                'a.package_id',
                'a.start_time',
                'a.end_time',
                'b.name',
            )
            ->where('a.user_id', $user_id)
            ->where('a.status', self::STATUS_ACTIVE)
            ->orderBy('a.id', 'desc');
        $datas = $query->paginate($perPage, "*", "page", $page)->toArray();
        return $datas;
    }
}
