<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 25/10/2022
 * Time: 08:50
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Redis;

class RedisLogRequestResponse{
    const WEB_KEY = 'WEB';
    const APP_KEY = 'APP';
    const API_KEY = 'API';
    const LOG_REQUEST_KEY = 'log_request';
    const LOG_RESPONSE_KEY = 'log_response';

    public static function store($request_id, $arr, $app=self::WEB_KEY, $type=self::LOG_REQUEST_KEY)
    {
        $time = 60 * 60;
        $key = now()->format('Y-m-d');
        $ckey = ''.$app.'_'.$type.':' . $key;
        Redis::hmset($ckey, $request_id, json_encode($arr));
        Redis::expire($ckey, $time);
    }

    public static function find($date, $app=self::WEB_KEY, $type=self::LOG_REQUEST_KEY)
    {
        $key = ''.$app.'_'.$type.':' . $date;
        $stored = Redis::hgetall($key);
        if (!empty($stored)) {
            return $stored;
        }
        return false;
    }

    public static function getAll($app=self::WEB_KEY, $type=self::LOG_REQUEST_KEY)
    {
        $keys = Redis::keys(''.$app.'_'.$type.':*');
        $clients = [];
        foreach ($keys as $key) {
            $client = Redis::hgetall($key);
            $clients[] = $client;
        }
        return $clients;
    }
}