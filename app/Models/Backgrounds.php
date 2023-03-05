<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backgrounds extends Model
{
    protected $fillable = [
        'thumbnail',
        'youtube_url',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public function scopeGetBackground()
    {
        return $this->where('deleted', false);
    }

    public static  function getBackground()
    {
        return self::where('deleted', false)->where('status', self::STATUS_ACTIVE)->select('thumbnail', 'youtube_url')->orderBy('created_at', 'desc')->first();
    }
}
