<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImplementationProcess extends Model
{
    protected $table = "implementation_process";
    protected $fillable = [
        'title',
        'content',
        'thumbnail',
        'youtube_url',
        'status',
        'ordinal',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted',

    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status'];
    public static  function getImplementationProcess()
    {
        return self::where('deleted', false)->where('status', self::STATUS_ACTIVE)->orderBY('ordinal', 'asc')->get();
    }
}
