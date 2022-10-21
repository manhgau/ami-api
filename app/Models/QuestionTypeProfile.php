<?php

namespace App\Models;

/*use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;*/

class QuestionTypeProfile
{

    const FULLNAME                                      = 'fullname';
    const YEAR_OF_BIRTH                                 = 'year_of_birth';
    const PROVINCE                                      = 'province';
    const GENDER                                        = 'gender';
    const MARITAL_STATUS                                = 'marital_status';
    const JOB_TYPE                                      = 'job_type';
    const PERSONAL_INCOME_LEVEL                         = 'personal_income_level';
    const FAMILY_INCOME_LEVEL                           = 'family_income_level';
    const FAMILY_PEOPLE                                 = 'family_people';
    const IS_KEY_SHOPPER                                = 'is_key_shopper';
    const HAS_CHILDREN                                  = 'has_children';
    const ACADEMIC_LEVEL                                = 'academic_level';

    // public static function getQuestionTypeProfile()
    // {
    //     return [
    //         self::FULLNAME,
    //         self::YEAR_OF_BIRTH,
    //         self::PROVINCE,
    //         self::GENDER,
    //         self::MARITAL_STATUS,
    //         self::JOB_TYPE,
    //         self::PERSONAL_INCOME_LEVEL,
    //         self::FAMILY_INCOME_LEVEL,
    //         self::FAMILY_PEOPLE,
    //         self::IS_KEY_SHOPPER,
    //         self::HAS_CHILDREN,
    //         self::ACADEMIC_LEVEL,
    //     ];
    // }

    public static function getQuestionTypeProfile()
    {
        return [
            ['question_type_profile' => self::FULLNAME, 'name' => 'Họ tên'],
            ['question_type_profile' => self::YEAR_OF_BIRTH, 'name' => 'Năm sinh'],
            ['question_type_profile' => self::PROVINCE, 'name' => 'Địa chỉ'],
            ['question_type_profile' => self::GENDER, 'name' => 'Giới tính'],
            ['question_type_profile' => self::MARITAL_STATUS, 'name' => 'Hôn nhân'],
            ['question_type_profile' => self::JOB_TYPE, 'name' => 'Nghề nghiệp'],
            ['question_type_profile' => self::PERSONAL_INCOME_LEVEL, 'name' => 'Mức thu nhập cá nhân'],
            ['question_type_profile' => self::FAMILY_INCOME_LEVEL, 'name' => 'Mức thu nhập cả hộ'],
            ['question_type_profile' => self::FAMILY_PEOPLE, 'name' => 'Số người trong gia đình'],
            ['question_type_profile' => self::IS_KEY_SHOPPER, 'name' => 'Trách nhiệm mua sắm'],
            ['question_type_profile' => self::HAS_CHILDREN, 'name' => 'Gia đình có trẻ em'],
            ['question_type_profile' => self::ACADEMIC_LEVEL, 'name' => 'Học vấn'],
        ];
    }
}
