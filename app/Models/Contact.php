<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'user_id',
        'fullname',
        'phone',
        'email',
        'company_name',
        'business_id',
        'job_type_id',
        'status',
        'deleted',
        'note',
        'message'
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at'];
    const PENDING = 'pending';
    const SUCCESS = 'success';
    const DESTROY = 'destroy';
    const NOT_DELETED  = 0;
    const DELETED  = 1;
}
