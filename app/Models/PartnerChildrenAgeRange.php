<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerChildrenAgeRange extends Model
{
    protected $fillable = [
        'partner_id',
        'childrend_age_range_id',
        'created_at',
        'updated_at',
    ];
}

