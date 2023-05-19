<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrequentlyQuestions extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'type',
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
    const CLIENT_WEB  = 'client_web';
    const PARTNER_APP  = 'partner_app';

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status'];
    public static  function getFrequentlyQuestion($perPage = 10,  $page = 1, $type = null)
    {
        $query =  self::where('deleted', false)
            ->where('status', self::STATUS_ACTIVE)
            ->orderBY('id', 'asc');
        if ($type != null) {
            $query->where('type', $type);
        }
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }
}
