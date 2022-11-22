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
                CURLOPT_CUSTOMREQUEST => $method,
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

    public static function parse_jsobj($str, &$data) {
        $str = trim($str);
        if(strlen($str) < 1) return;

        if($str{0} != '{') {
            throw new \Exception('The given string is not a JS object');
        }
        $str = substr($str, 1);

        /* While we have data, and it's not the end of this dict (the comma is needed for nested dicts) */
        while(strlen($str) && $str{0} != '}' && $str{0} != ',') {
            /* find the key */
            if($str{0} == "'" || $str{0} == '"') {
                /* quoted key */
                list($str, $key) = self::__parse_jsdata($str, ':');
            } else {
                $match = null;
                /* unquoted key */
                if(!preg_match('/^\s*[a-zA-z_][a-zA-Z_\d]*\s*:/', $str, $match)) {
                    throw new \Exception('Invalid key ("'.$str.'")');
                }
                $key = $match[0];
                $str = substr($str, strlen($key));
                $key = trim(substr($key, 0, -1)); /* discard the ':' */
            }

            list($str, $data[$key]) = self::__parse_jsdata($str, '}');
        }
        "Finshed dict. Str: '$str'\n";
        return substr($str, 1);
    }

    private static function __comma_or_term_pos($str, $term) {
        $cpos = strpos($str, ',');
        $tpos = strpos($str, $term);
        if($cpos === false && $tpos === false) {
            throw new \Exception('unterminated dict or array');
        } else if($cpos === false) {
            return $tpos;
        } else if($tpos === false) {
            return $cpos;
        }
        return min($tpos, $cpos);
    }

    private static function __parse_jsdata($str, $term="}") {
        $str = trim($str);


        if(is_numeric($str{0}."0")) {
            /* a number (int or float) */
            $newpos = self::__comma_or_term_pos($str, $term);
            $num = trim(substr($str, 0, $newpos));
            $str = substr($str, $newpos+1); /* discard num and comma */
            if(!is_numeric($num)) {
                throw new \Exception('OOPSIE while parsing number: "'.$num.'"');
            }
            return array(trim($str), $num+0);
        } else if($str{0} == '"' || $str{0} == "'") {
            /* string */
            $q = $str{0};
            $offset = 1;
            do {
                $pos = strpos($str, $q, $offset);
                $offset = $pos;
            } while($str{$pos-1} == '\\'); /* find un-escaped quote */
            $data = substr($str, 1, $pos-1);
            $str = substr($str, $pos);
            $pos = self::__comma_or_term_pos($str, $term);
            $str = substr($str, $pos+1);
            return array(trim($str), $data);
        } else if($str{0} == '{') {
            /* dict */
            $data = array();
            $str = self::parse_jsobj($str, $data);
            return array($str, $data);
        } else if($str{0} == '[') {
            /* array */
            $arr = array();
            $str = substr($str, 1);
            while(strlen($str) && $str{0} != $term && $str{0} != ',') {
                $val = null;
                list($str, $val) = self::parse_jsdata($str, ']');
                $arr[] = $val;
                $str = trim($str);
            }
            $str = trim(substr($str, 1));
            return array($str, $arr);
        } else if(stripos($str, 'true') === 0) {
            /* true */
            $pos = self::__comma_or_term_pos($str, $term);
            $str = substr($str, $pos+1); /* discard terminator */
            return array(trim($str), true);
        } else if(stripos($str, 'false') === 0) {
            /* false */
            $pos = self::__comma_or_term_pos($str, $term);
            $str = substr($str, $pos+1); /* discard terminator */
            return array(trim($str), false);
        } else if(stripos($str, 'null') === 0) {
            /* null */
            $pos = self::__comma_or_term_pos($str, $term);
            $str = substr($str, $pos+1); /* discard terminator */
            return array(trim($str), null);
        } else if(strpos($str, 'undefined') === 0) {
            /* null */
            $pos = self::__comma_or_term_pos($str, $term);
            $str = substr($str, $pos+1); /* discard terminator */
            return array(trim($str), null);
        } else {
            throw new \Exception('Cannot figure out how to parse "'.$str.'" (term is '.$term.')');
        }
    }
}
