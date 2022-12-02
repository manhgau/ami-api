<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaritalStatus extends Model
{
    protected $table = 'marital_status';
    protected $fillable = [
        'name',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getListMaritalStatus($perPage = 10,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->where('status', self::STATUS_ACTIVE)->paginate($perPage, "*", "page", $page)->toArray();
    }
    public static  function getAllMaritalStatus()
    {
        return self::select('id as value', 'name')->where('deleted', self::NOT_DELETED)->get();
    }
    public static  function getMaritalStatus($perPage = 100,  $page = 1,  $name = null)
    {
        $family_people = self::where('deleted', self::NOT_DELETED)
            ->orderBy('id', 'ASC')
            ->where('status', self::STATUS_ACTIVE);
        if ($name != null) {
            $family_people->where('name', 'like', '%' . $name . '%');
        }

        $data =  $family_people->paginate($perPage, "*", "page", $page)->toArray();
        return $data;
    }
}
