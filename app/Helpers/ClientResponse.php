<?php

namespace App\Helpers;

use App\Helpers\RedisLogRequestResponse;
use Illuminate\Support\Str;
use App\Helpers\Context;

class ClientResponse
{

    public static $error_code = 400;
    public static $success_code = 200;    //OK
    public static $required_login_code = 401;     //Unauthorized
    public static $user_not_active = 402;     //TK chưa kích hoạt
    public static $required_refresh_token = 403;     //user phải gọi refresh token
    public static $validator_value = 405;     //user phải gọi refresh token
    public static $client_auth_owner_survey = 407;     //Khảo sát không phải của bạn
    public static $survey_user_number = 406;     //Số lượng khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm lượt tạo khảo sát
    public static $add_logo = 408;     //Bạn không có quyền thêm logo, Vui lòng đăng ký gói cước để sử dụng chứ năng này
    public static $app_is_maintain = 409;     //Hệ thống đang bảo trì

    public static $survey_enough_responses = 301;     //Khảo sát đã thu thập đủ số phản hồi,hoặc đã đóng
    // public static $package_limit_expires = 302;     //Hạn mức phản hồi của gói đã hết

    //
    public static $partner_required_fill_info = 421;
    //Partner cập nhật hồ sơ cá nhân
    public static $check_partner_mapping_fcmtoken = 420;
    public static $partner_input = 600;     //Partner đã trả lời khảo sát
    public static $un_partner_input = 601;     //Partner chưa trả lời khảo sát
    public static $limmit_of_response = 602;     //Khảo sát đã đạt lượt phản hồ giới hạn

    public static function response($code, $message, $data = [], $headers = [], $options = [])
    {
        $data = [
            'code'      =>  $code,
            'message'   =>  $message,
            'data'      =>  $data,
            /*'headers'   =>  $headers,
            'options'   =>  $options*/
        ];

        self::__log_response_data($data, $code);
        return response()->json($data, self::$success_code);
    }

    public static function responseSuccess($message, $data = [])
    {
        return self::response(self::$success_code, $message, $data);
    }

    public static function responseError($message, $data = [])
    {
        return self::response(self::$error_code, $message, $data);
    }

    private function __log_response_data($data = [], $code = '')
    {
        $is_debug = env('LOG_DEBUG');
        if ($is_debug) {
            $resquest_id = Context::getInstance()->get(Context::REQUEST_ID) ?? Str::uuid();
            $dateString = now()->format('Y-m-d H:i:s');
            $req = $data;
            $req['dateString'] = $dateString;
            $req['code'] = $code;
            RedisLogRequestResponse::store($resquest_id, $req, RedisLogRequestResponse::API_KEY, RedisLogRequestResponse::LOG_RESPONSE_KEY);
        }
    }
}
