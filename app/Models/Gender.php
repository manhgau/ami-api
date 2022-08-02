<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genders extends Model
{
    protected $fillable = [
        'name',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getGenders($perPage = 100,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->paginate($perPage, "*", "page", $page)->toArray();
    }

}

