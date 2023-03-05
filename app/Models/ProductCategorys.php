<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategorys extends Model
{

    protected $fillable = [
        'title',
        'slug',
        'thumbnail',
        'image',
        'youtube_url',
        'description',
        'content',
        'status',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status'];
    public static  function getAllProductCategory($perPage = 10,  $page = 1)
    {
        return self::select('id', 'title', 'thumbnail', 'youtube_url', 'description')->where('deleted', self::NOT_DELETED)->orderBy('id', 'asc')->where('status', self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailProductCategory($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('status', self::STATUS_ACTIVE)->first();
    }
}
