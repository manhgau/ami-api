<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QAndACategory extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status'];

    public static  function getALL($perPage = 10,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'asc')->where('status', self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetail($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('status', self::STATUS_ACTIVE)->first();
    }
}
