<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QAndAItems extends Model
{
    protected $fillable = [
        'title',
        'category_id',
        'description',
        'icon',
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

    public static  function getALL($perPage = 10,  $page = 1, $category_id)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'asc')->where('category_id', $category_id)->where('status', self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }
}
