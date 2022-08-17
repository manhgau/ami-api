<?php

namespace App\Helpers;
class ClientResponse{

    public static $error_code = 400;
    public static $success_code = 200;    //OK
    public static $required_login_code = 401;     //Unauthorized
    public static $user_not_active = 402;     //TK chưa kích hoạt
    public static $required_refresh_token = 403;     //user phải gọi refresh token
    public static $client_auth_owner_survey = 407;     //Khảo sát không phải của bạn
    public static $survey_user_number = 406;     //Số lượng khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm lượt tạo khảo sát
    //
    public static $partner_required_fill_info = 421;     //Partner cập nhật hồ sơ cá nhân

    public static function response($code, $message, $data = [],$headers=[], $options=[]){
        return response()->json([
            'code'      =>  $code,
            'message'   =>  $message,
            'data'      =>  $data,
            /*'headers'   =>  $headers,
            'options'   =>  $options*/
        ], $code);
    }

    public static function responseSuccess($message, $data = []){
        return self::response(self::$success_code, $message, $data);
    }

    public static function responseError($message, $data = []){
        return self::response(self::$error_code, $message, $data);
    }
}
