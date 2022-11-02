<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerProfile extends Model
{
    protected $fillable = [
        'partner_id',
        'point',
        'kpi_point',
        'fullname',
        'phone',
        'year_of_birth',
        'gender',
        'province_code',
        'district_code',
        'addrees',
        'job_type_id',
        'academic_level_id',
        'marital_status_id',
        'personal_income_level_id',
        'family_income_level_id',
        'family_people',
        'has_children',
        'is_key_shopper',
        'note',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static  function updatePartnerProfile($data, $id)
    {
        return  self::where('partner_id', $id)->update($data);
    }

    public static  function getDetailPartnerProfile($partner_id)
    {
        return self::where('partner_id', $partner_id)->first();
    }

    public static  function getPartnerProfileDetail($partner_id)
    {
        return self::select(
            'fullname',
            'year_of_birth',
            'gender',
            'province_code',
            'job_type_id',
            'academic_level_id',
            'marital_status_id',
            'personal_income_level_id',
            'family_income_level_id',
            'family_people',
            'has_children',
            'is_key_shopper',
        )
            ->where('partner_id', $partner_id)->first()->toArray();
    }
}
