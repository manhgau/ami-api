<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyIncomeLevels extends Model
{
    protected $fillable = [
        'name',
        'min_value',
        'max_value',
    ];
    protected $hidden = ['deleted', 'created_at', 'updated_at', 'status'];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

}

