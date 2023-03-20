<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyCategory extends Model
{
    protected $fillable = [
        'title',
        'icon',
        'status',
        'created_at',
        'updated_at',
        'deleted',
    ];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    protected $hidden = ['deleted', 'created_at', 'updated_at'];
    public static  function getALL()
    {
        return self::where('deleted', self::NOT_DELETED)->where('status', self::STATUS_ACTIVE)->orderBy('id', 'asc')->get()->toArray();
    }
}
