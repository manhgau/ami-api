<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 21/07/2022
 * Time: 13:48
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    public static function sendSms($phone, $content)
    {
        $status = -1;
        $message = 'Không thể gửi sms';
        try{
            $logs = new SmsLog();
            $logs->phone = $phone;
            $logs->content = $content;

            $rs = self::__sendSmsViaApi($phone, $content);
            if(isset($rs['status']) && $rs['status']==1){
                $logs->sent = SmsLog::SENT_SUCCESS;
                $message = 'Gửi tin sms thành công tới số: '.$phone.'';
                $logs->note = $message;
                $status = 1;
            }else{
                $logs->sent = SmsLog::SENT_ERROR;
                $message = $rs['message']??'Gửi tin nhắn không thành công';
                $logs->note = $message;
            }
            $logs->save();
        }catch (\Exception $ex){
            $message = $ex->getMessage();
        }
        return [
            'status' => $status,
            'message' => $message
        ];
    }


    private static function __sendSmsViaApi($phone, $content)
    {
        $status = 1;
        $message = 'Gửi OTP thành công';
        return [
            'status' => $status,
            'message' => $message
        ];
    }

    public static function generateRegisterSms($otp){
        return 'Ma kich hoat cua ban la: '.$otp.'. Ma kich hoat co hieu luc trong '.Otp::OTP_EXPIRE.' phut.';
    }

    public static function generateForgotPasswordSms($otp){
        return 'Ma dat lai mat khau cua ban la: '.$otp.'. Ma kich hoat co hieu luc trong '.Otp::OTP_EXPIRE.' phut.';
    }

}