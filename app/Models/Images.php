<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Images extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'image',
        'background_type',
        'size',
        'upload_ip',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const TEMPLATE = 1;
    const NO_TEMPLATE = 0;

    public static  function getTemplateImage($perPage = 100,  $page = 1, $type = null)
    {
        $query =  self::where('deleted', self::NOT_DELETED)->where('template_image', self::TEMPLATE);
        if ($type != null) {
            $query->where('background_type', $type);
        }
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailImage($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->first();
    }
}
