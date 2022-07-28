<?php
namespace App\Helpers\Common;

use Cache;

class CommonCached {

    const EXPIRE_FAST                           = 120; //2 minutes
    const EXPIRE_SLOW                           = 1200; //20 minutes

    const api_list_province                                 = 'api_cached:api_list_province';
    const cache_find_blog_category_by_id                    = "api_cached:cache_find_blog_category_by_id:id:";
    const cache_find_blog_by_slug                           = 'api_cached:cache_find_blog_by_slug:slug';


    public static function storeData($key_cache, $datas, $fast = false){
        $time = $fast?self::EXPIRE_FAST:self::EXPIRE_SLOW;
        Cache::set($key_cache, $datas, $time);
    }

    public static function getData($key_cache){
        $datas = Cache::get($key_cache);
        return $datas;
    }

    public static function removeData($key_cache){
        Cache::forget($key_cache);
    }
}
