<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPages extends Model
{

    protected $fillable = [
        'title',
        'slug',
        'name_menu',
        'content',
        'created_at',
        'updated_at',
        'deleted',
    ];

    protected $hidden = ['deleted',  'updated_at'];
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getStaticPagesBySlug($slug)
    {
        return self::where('deleted', self::NOT_DELETED)->where('slug', $slug)->first();
    }
}
