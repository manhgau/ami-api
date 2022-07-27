<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicLevel extends Model
{
    protected $fillable = [
        'name',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getAcademicLevel($perPage = 100,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->where('status',self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }


}

