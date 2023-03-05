<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductItems extends Model
{

    protected $fillable = [
        'title',
        'category_id',
        'icon',
        'description',
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

    public static  function getAllProductItem($perPage = 10,  $page = 1, $category_id)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'asc')->where('category_id', $category_id)->where('status', self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }
}
