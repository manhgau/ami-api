<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscribes extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'status',
        'deleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function updateContacts($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->update($data);
    }

    public function scopeGetSubscribes()
    {
        return $this->where('deleted', self::NOT_DELETED);
    }
}
