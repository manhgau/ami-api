<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
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
        'deleted',
        'type'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const BLOG = 'blog';
    const SOLUTION = 'solution';

    public static function getType()
    {
        return [
            self::BLOG,
            self::SOLUTION,
        ];
    }

    public static function checkTypeValid($type)
    {
        $list = self::getType();
        if (in_array($type, $list)) {
            return true;
        } else {
            return false;
        }
    }

    public static  function getALL($perPage = 10,  $page = 1, $type)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->where('type', $type)
            ->where('status', self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetail($id)
    {

        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->where('status', self::STATUS_ACTIVE)->first();
    }
}
