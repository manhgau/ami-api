<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{

    protected $fillable = [
        'user_id',
        'package_id',
        'start_time',
        'end_time',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static  function updatePackage($data, $id)
    {
        return self::where('id', $id)->update($data);
    }
    public function userCreated()
    {
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    public function userPackage()
    {
        return $this->belongsTo('App\Models\UserClient','user_id','id');
    }

    public function Package()
    {
        return $this->belongsTo('App\Models\Package');
    }

    public function userUpdated()
    {
        return $this->belongsTo('App\Models\User', 'updated_by', 'id');
    }

}
