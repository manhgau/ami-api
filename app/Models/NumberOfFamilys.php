<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberOfFamilys extends Model
{

    protected $fillable = [
        'id',
        'name',
        'min_value',
        'max_value',
        'status',
        'deleted',
        'created_at',
        'updated_at',
    ];

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;


    public static  function getFamilyPeople($perPage = 100,  $page = 1,  $name = null)
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
