<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'created_at',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getALL($perPage = 10,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->where('status',self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetail( $slug)
    {
        return self::where('deleted', self::NOT_DELETED)->where('slug', $slug)->where('status',self::STATUS_ACTIVE)->first();
    }


}

