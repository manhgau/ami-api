<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static  function checkCompletePartnerProfile($partner_id)
    {
        return self::where('partner_id', $partner_id)
            ->whereNotNull('fullname')
            ->whereNotNull('year_of_birth')
            ->whereNotNull('gender')
            ->whereNotNull('province_code')
            ->whereNotNull('district_code')
            ->whereNotNull('addrees')
            ->whereNotNull('job_type_id')
            ->whereNotNull('academic_level_id')
            ->whereNotNull('marital_status_id')
            ->whereNotNull('personal_income_level_id')
            ->whereNotNull('family_income_level_id')
            ->whereNotNull('family_people')
            ->whereNotNull('has_children')
            ->whereNotNull('is_key_shopper')
            ->count();
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

    public static  function getPartnerProfile($partner_id)
    {
        return  DB::table('partner_profiles as a')
            ->leftJoin('districts as b', 'b.code', '=', 'a.district_code')
            ->leftJoin('genders as c', 'c.id', '=', 'a.gender')
            ->leftJoin('provinces as d', 'd.code', '=', 'a.province_code')
            ->leftJoin('job_types as e', 'e.id', '=', 'a.job_type_id')
            ->leftJoin('academic_levels as f', 'f.id', '=', 'a.academic_level_id')
            ->leftJoin('marital_status as g', 'g.id', '=', 'a.marital_status_id')
            ->leftJoin('personal_income_levels as h', 'h.id', '=', 'a.personal_income_level_id')
            ->leftJoin('family_income_levels as i', 'i.id', '=', 'a.family_income_level_id')
            ->leftJoin('number_of_familys as k', 'k.id', '=', 'a.family_people')
            ->select(
                'a.id',
                'a.partner_id',
                'a.fullname',
                'a.avatar',
                'a.year_of_birth',
                'a.phone',
                'a.point',
                'a.point_used',
                'a.kpi_point',
                'a.province_code',
                'a.district_code',
                'a.gender',
                'a.job_type_id',
                'a.academic_level_id',
                'a.marital_status_id',
                'a.personal_income_level_id',
                'a.family_income_level_id',
                'a.family_people as family_people_id ',
                'a.has_children',
                'a.is_key_shopper',
                'b.name as district_name',
                'c.name as gender_name',
                'd.name as province_name',
                'e.name as job_type_name',
                'f.name as academic_level_name',
                'g.name as .marital_status_name',
                'h.name as personal_income_level_name',
                'i.name as family_income_level_name',
                'k.min_value as family_people',
            )
            ->where('partner_id', $partner_id)->first();
    }
}
