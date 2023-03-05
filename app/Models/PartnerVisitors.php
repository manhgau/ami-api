<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerVisitors extends Model
{
    protected $fillable = [
        'name',
        'thumbnail',
        'status',
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
    public static  function getPartnerVisitor($perPage = 10,  $page = 1)
    {
        return self::where('deleted', false)->where('status', self::STATUS_ACTIVE)->orderBY('id', 'asc')->paginate($perPage, "*", "page", $page)->toArray();
    }
}
