<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getProvince($perPage = 100,  $page = 1, $name = null)
    {
        $provinces = self::where('deleted', self::NOT_DELETED)->orderBy('id', 'ASC')->where('status', self::STATUS_ACTIVE);
        if ($name != null) {
            $provinces->where('name', 'like', '%' . $name . '%');
        }

        $data =  $provinces->paginate($perPage, "*", "page", $page)->toArray();
        return $data;
    }
}
