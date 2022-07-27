<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobStatus extends Model
{
    protected $fillable = [
        'name',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getJobStatus($perPage = 100,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->where('status',self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }

}

