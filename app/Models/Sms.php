<?php

/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 21/07/2022
 * Time: 13:48
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Common\CFunction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Sms extends Model
{
    const ONE_SMS_KEY_CODE = 'code';
    const ONE_SMS_SUCCESS_CODE = 0;

    public static function sendSms($phone, $content)
    {
        $status = -1;
        $message = 'Không thể gửi sms';
        try {
            $logs = new SmsLog();
            $logs->phone = $phone;
            $logs->content = $content;

            $rs = self::__sendSmsViaApi($phone, $content);
            if (isset($rs['status']) && $rs['status'] == 1) {
                $logs->sent = SmsLog::SENT_SUCCESS;
                $message = 'Gửi tin sms thành công tới số: ' . $phone . '';
                $logs->note = $message;
                $status = 1;
            } else {
                $logs->sent = SmsLog::SENT_ERROR;
                $message = $rs['message'] ?? 'Gửi tin nhắn không thành công';
                $logs->note = $message;
            }
            $logs->save();
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }


    private static function __sendSmsViaApi($phone, $content)
    {
        return self::__sendSMSViaOneSms($phone, $content);
    }

    public static function generateRegisterSms($otp)
    {
        return 'Ma kich hoat cua ban la: ' . $otp . '. Ma kich hoat co hieu luc trong ' . Otp::OTP_EXPIRE . ' phut.';
    }

    public static function generateForgotPasswordSms($otp)
    {
        return 'Ma dat lai mat khau cua ban la: ' . $otp . '. Ma kich hoat co hieu luc trong ' . Otp::OTP_EXPIRE . ' phut.';
    }

    private static function __sendSMSViaOneSms($phone, $message)
    {
        try {
            $config = config('services.one_sms');
            $data = [
                "username" => $config['username'] ?? '',
                "password" => $config['password'] ?? '',
                "brandname" => $config['brandname'] ?? '',
                "phone" =>  $phone,
                "message" => $message
            ];
            $api_url = $config['api_url'] ?? '';
            $response = CFunction::curl_json($api_url, $data, "POST");
            if (isset($response['status']) && $response['status'] == 1) {
                $rs =  $response['result'] ?? null;
                $rs = json_decode($rs, false);
                dd($rs);
                if (isset($rs['' . self::ONE_SMS_KEY_CODE . '']) && $rs['' . self::ONE_SMS_KEY_CODE . ''] == self::ONE_SMS_SUCCESS_CODE) {
                    return [
                        'status'    => 1,
                        'message'    => 'Gửi tin nhắn thành công đến số: ' . $phone . ''
                    ];
                } else {
                    return [
                        'status'    => -1,
                        'message'    => $rs['message'] ?? 'Không thể gửi tin nhắn'
                    ];
                }
            } else {
                return [
                    'status'    => -1,
                    'message'    => $response['message'] ?? 'Lỗi không xác định'
                ];
            }
        } catch (\Exception $e) {
            $error =  $e->getMessage();
            Log::error($error);
            return [
                'status'    => -1,
                'message'    => $error
            ];
        }
    }
}
