<?php

namespace App\Helpers\Common;

use Cache;

class CommonCached
{

    const EXPIRE_FAST                                       = 120; //2 minutes
    const EXPIRE_SLOW                                       = 1200; //20 minutes
    //app settings
    const app_all_setting                                   = "api_cached:app_all_setting:";
    const api_get_info                                      = "api_cached:api_get_info:";

    //API
    const api_list_province                                 = 'api_cached:api_list_province';
    const api_list_district                                 = 'api_cached:api_list_district';
    const api_list_ward                                     = 'api_cached:api_list_ward';
    const api_template_image                                = 'api_cached:api_template_image';
    //
    const api_list_job_status                               = 'api_cached:api_list_job_status';
    const api_list_job_type                                 = 'api_cached:api_list_job_type';
    const api_list_business_scope                           = 'api_cached:api_list_business_scope';
    const api_list_academic_level                           = 'api_cached:api_list_academic_level';
    const api_list_family_income_levels                     = 'api_cached:api_list_family_income_levels';
    const api_list_children_age_ranges                      = 'api_cached:api_list_children_age_ranges';
    const api_list_personal_income_levels                   = 'api_cached:api_list_personal_income_levels';
    const api_list_genders                                  = 'api_cached:api_list_genders';
    const api_list_family_people                            = 'api_cached:api_list_family_people';
    const api_get_marital_status                            = 'api_cached:api_get_marital_status';
    const api_list_year_of_birth                            = 'api_cached:api_list_year_of_birth';
    //
    const cache_find_blog_category_by_id                    = "api_cached:cache_find_blog_category_by_id:id:";
    const cache_find_blog_category                          = "api_cached:cache_find_blog_category";
    const cache_find_blog_by_slug                           = 'api_cached:cache_find_blog_by_slug:slug';
    const cache_find_blog                                   = 'api_cached:cache_find_blog';
    const cache_find_blog_relate                            = 'api_cached:cache_find_blog_relate';
    //
    const cache_find_qa_category_by_id                      = "api_cached:cache_find_qa_category_by_id:id:";
    const cache_find_qa_category                            = "api_cached:cache_find_qa_category";
    const cache_find_qa_by_slug                             = 'api_cached:cache_find_qa_by_slug:slug';
    const cache_find_qa                                     = 'api_cached:cache_find_qa';
    const cache_find_qa_relate                              = 'api_cached:cache_find_qa_relate';
    //
    const cache_find_page_by_slug                           = 'api_cached:cache_find_page_by_slug:slug';
    const cache_find_page                                   = 'api_cached:cache_find_page';
    //
    const cache_find_package_by_id                          = 'api_cached:cache_find_package_by_id:id';
    const cache_find_package                                = 'api_cached:cache_find_package';
    const cache_find_feedback                               = 'api_cached:cache_find_feedback';

    //
    const cache_find_survey_category                         = 'api_cached:cache_find_survey_category';
    const cache_find_survey_user_by_id                       = 'api_cached:cache_find_survey_user_by_id:id';
    const cache_find_survey_user                             = 'api_cached:cache_find_survey_user';
    //
    const cache_find_survey_question_by_survey_id            = 'api_cached:cache_find_survey_question_by_survey_id';
    const get_list_question_by_survey_id                     = 'api_cached:get_list_question_by_survey_id';
    const cache_find_survey_question_by_question_id          = 'api_cached:cache_find_survey_question_by_question_id';
    //
    const cache_find_survey_template                         = 'api_cached:cache_find_survey_template';
    const cache_find_survey_template_by_id                   = 'api_cached:cache_find_survey_template_by_id';
    //
    const cache_find_survey_partner                          = 'api_cached:cache_find_survey_partner';



    public static function storeData($key_cache, $datas, $fast = false)
    {
        $time = $fast ? self::EXPIRE_FAST : self::EXPIRE_SLOW;
        Cache::set($key_cache, $datas, $time);
    }

    public static function getData($key_cache)
    {
        $datas = Cache::get($key_cache);
        return $datas;
    }

    public static function removeData($key_cache)
    {
        Cache::forget($key_cache);
    }
}
