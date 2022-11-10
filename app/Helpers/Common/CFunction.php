<?php

/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 21/07/2022
 * Time: 13:44
 */

namespace App\Helpers\Common;

use App\Models\AppSetting;
use Illuminate\Support\Str;

class CFunction
{
    /**
     * @param $phone
     * @return bool
     */
    public static function isPhoneNumber($phone)
    {
        if (is_numeric($phone) && (strlen($phone) == 10) && (substr($phone, 0, 1) == '0')) {
            return true;
        }
        return false;
    }

    public static function getFrontendWebUrl()
    {
        return AppSetting::getByKey(AppSetting::FRONTEND_WEB_URL);
    }

    public static function generateUuid()
    {
        return (string) Str::uuid();
    }


    //method: GET, POST, PUT, DELETE
    public static function curl_json($url, $data = [], $method = "POST", $timeout = 10)
    {
        try {
            $curl = curl_init();
            $data = json_encode($data);
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST  => FALSE,
                CURLOPT_SSL_VERIFYPEER  => FALSE,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Accept: application/json'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return [
                'status'    => 1,
                'result'    => $response,
                'message' => 'Thành công'
            ];
        } catch (\Exception $e) {
            return [
                'status'    => -1,
                'result'    => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
