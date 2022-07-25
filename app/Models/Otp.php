<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 21/07/2022
 * Time: 13:48
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class Otp extends Model{
    const TYPE_REGISTER = 'register';
    const TYPE_FORGOT_PASSWORD = 'forgot_password';

    const OTP_LENGTH = 6;
    const OTP_EXPIRE = 3; //3 phút
    const MAX_OTP_BY_PHONE_PER_DAY = 10; //gửi tối đa 10 otp cho 1 số điện thoại trong 1 ngày

    private static $__default_phone_otp_arr = ['0392565507','0963760289','0587113333'];
    private static $_default_otp = '123456';


    /**
     * @param $phone
     * @param $content
     * @return array
     * Tạo OTP, gửi sms và lưu log otp
     */
    public static function sendOtpToPhone($otp, $phone, $content=''){
        $status = -1; $message = 'Không thể gửi OTP';
        try{
            //step 1: check otp đã gửi quá giới hạn?
            $otp_sent_by_phone = self::__countOtpSentByPhone($phone);
            $otp_max_by_phone = self::__getMaxOtpByPhone($phone);
            if($otp_sent_by_phone < $otp_max_by_phone){
                $logs = new OtpLog();
                $logs->phone = $phone;
                $logs->otp = $otp;
                $logs->expire_at = time() + (self::OTP_EXPIRE * 60);

                //step 2: Send otp via sms and save logs
                $rs = Sms::sendSms($phone, $content);
                if(isset($rs['status']) && $rs['status']==1){
                    $message = 'Đã gửi otp tới số điện thoại: '.$phone.'';
                    $status = 1;
                    $logs->sent = OtpLog::SENT_SUCCESS;
                }else{
                    $logs->sent = OtpLog::SENT_ERROR;
                    $message = $rs['message']??'Không thể gửi otp';
                }
                $logs->note = $message;
                $logs->save();
            }else{
                $message = 'Đã quá giới hạn gửi OTP trong ngày';
            }
        }catch (\Exception $ex){
            $message = $ex->getMessage();
        }

        return [
            'status'    =>  $status,
            'message'   =>  $message
        ];
    }

    public static function validateOtpByPhone($otp, $phone){
        $status = -1; $message = 'OTP không hợp lệ';
        $rs = self::getOtpByPhone($otp, $phone);
        if($rs){
            if($rs['expire_at'] > time()){
                $status = 1;
                $message = 'OTP hợp lệ';
            }else{
                $message = 'OTP đã hết hạn';
            }
        }else{
            $message = 'OTP không tồn tại';
        }
        return [
            'status'    =>  $status,
            'message'   =>  $message,
        ];
    }

    public static function getOtpByPhone($otp, $phone){
        return OtpLog::where('phone', $phone)->where('otp', $otp)->orderByDesc('id')->first();
    }

    public static function genOtp($phone='', $length = self::OTP_LENGTH){
        if(in_array($phone, self::$__default_phone_otp_arr)){
            return self::$_default_otp;
        }else {
            return rand(100000, 999999);
        }
    }

    private static function __countOtpSentByPhone($phone){
        return self::__countOtpSentByPhonePerDay($phone);
    }

    private static function __countOtpSentByPhonePerDay($phone){
        $from = date('Y-m-d 00:00:00');
        $to = date('Y-m-d 23:59:59');

        return OtpLog::where('phone', $phone)->whereBetween('created_at', [$from, $to])->count();
    }

    private static function __getMaxOtpByPhone($phone){
        return self::__getMaxOtpByPhonePerDay($phone);
    }

    private static function __getMaxOtpByPhonePerDay($phone){
        return self::MAX_OTP_BY_PHONE_PER_DAY;
    }

}