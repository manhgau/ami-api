<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerProfile extends Model
{
    protected $fillable = [
        'partner_id',
        'point',
        'kpi_point',
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
        'note',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function updatePartnerProfile($data, $id)
    {
        return self::whre('delete', self::DELETED)->where('id', $id)->update($data);
    }
}
