<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status', 'province_code'];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getDistrict($perPage = 100,  $page = 1,  $name = null, $province_code)
    {
        $districts = self::where('deleted', self::NOT_DELETED)->where('province_code', $province_code)->orderBy('id', 'ASC')->where('status',self::STATUS_ACTIVE);
        if ($name != null) {
            $districts->where('name', 'like', '%' . $name . '%');
        }

        $data =  $districts->paginate($perPage, "*", "page", $page)->toArray();
        return $data;
    }




}

