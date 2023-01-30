<?php

namespace App\Models;

/*use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;*/

class QuestionTypeProfile
{

    const FULLNAME                                      = 'fullname';
    const YEAR_OF_BIRTH                                 = 'year_of_birth';
    const PROVINCE                                      = 'province_code';
    const DISTRICT                                      = 'district_code';
    const GENDER                                        = 'gender';
    const MARITAL_STATUS                                = 'marital_status_id';
    const JOB_TYPE                                      = 'job_type_id';
    const PERSONAL_INCOME_LEVEL                         = 'personal_income_level_id';
    const FAMILY_INCOME_LEVEL                           = 'family_income_level_id';
    const FAMILY_PEOPLE                                 = 'family_people';
    const IS_KEY_SHOPPER                                = 'is_key_shopper';
    const HAS_CHILDREN                                  = 'has_children';
    const ACADEMIC_LEVEL                                = 'academic_level_id';

    public static function getTypeProfile()
    {
        return [
            self::FULLNAME              => 'Họ tên',
            self::YEAR_OF_BIRTH         => 'Độ tuổi',
            self::PROVINCE              => 'Địa chỉ',
            self::GENDER                => 'Giới tính',
            self::MARITAL_STATUS        => 'Hôn nhân',
            self::JOB_TYPE              => 'Nghề nghiệp',
            self::FAMILY_INCOME_LEVEL   => 'Mức thu nhâp cả hộ',
            self::FAMILY_PEOPLE         => 'Số người trong gia đình',
            self::IS_KEY_SHOPPER        => 'Có chịu trách nhiệm mua sắm chính không',
            self::HAS_CHILDREN          => 'Gia đình có trẻ con không',
            self::ACADEMIC_LEVEL        => 'Trình độ học vấn',
        ];
    }

    public static function getTypeTarget()
    {
        return [
            ['type_target' => self::FULLNAME, 'name' => 'Họ tên'],
            ['type_target' => self::YEAR_OF_BIRTH, 'name' => 'Năm sinh'],
            ['question_type_profile' => self::PROVINCE, 'name' => 'Tỉnh/Tp'],
            ['question_type_profile' => self::DISTRICT, 'name' => 'Quận/Huyện'],
            ['type_target' => self::GENDER, 'name' => 'Giới tính'],
            ['type_target' => self::MARITAL_STATUS, 'name' => 'Hôn nhân'],
            ['type_target' => self::JOB_TYPE, 'name' => 'Nghề nghiệp'],
            ['type_target' => self::PERSONAL_INCOME_LEVEL, 'name' => 'Mức thu nhập cá nhân'],
            ['type_target' => self::FAMILY_INCOME_LEVEL, 'name' => 'Mức thu nhập cả hộ'],
            ['type_target' => self::FAMILY_PEOPLE, 'name' => 'Số người trong gia đình'],
            ['type_target' => self::IS_KEY_SHOPPER, 'name' => 'Trách nhiệm mua sắm'],
            ['type_target' => self::HAS_CHILDREN, 'name' => 'Gia đình có trẻ em'],
            ['type_target' => self::ACADEMIC_LEVEL, 'name' => 'Học vấn'],
        ];
    }
}
