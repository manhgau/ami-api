<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status', 'district_code'];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getWard($perPage = 100,  $page = 1, $name = null, $district_code)
    {
        $wards = self::where('deleted', self::NOT_DELETED)->where('district_code', $district_code)->orderBy('id', 'ASC')->where('status',self::STATUS_ACTIVE);
        if ($name != null) {
            $wards->where('name', 'like', '%' . $name . '%');
        }

        $data =  $wards->paginate($perPage, "*", "page", $page)->toArray();
        return $data;
    }


}

