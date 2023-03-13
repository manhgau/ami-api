<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerContacts extends Model
{
    protected $fillable = [
        'name',
        'email_phone',
        'year_of_birth',
        'gender',
        'province_code',
        'district_code',
        'ward_code',
        'job_type_id',
        'job_status_id',
        'academic_level_id',
        'marital_status_id',
        'personal_income_level_id',
        'family_income_level_id',
        'family_people',
        'has_children',
        'is_key_shopper',
        'most_cost_of_living',
        'childrend_age_ranges',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
}
