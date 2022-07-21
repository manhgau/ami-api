<?php

namespace App\Http\Controllers\v1\partner;

use App\Models\OtpLog;
use Illuminate\Http\Request;

use App\Models\Partner;
use App\Models\Otp;
use App\Models\Sms;
use App\Models\PartnerAccessToken;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CFunction;
use DB;
use App\Helpers\JWT;
use App\Helpers\Context;

class AuthController extends Controller
{

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return ClientResponse::responseError('Vui lòng nhập số điện thoại và mật khẩu');
        }
        $phone = $request->phone;
        $password = $request->password;
        $partner = Partner::loginAttempByPhone($phone, $password);

        if ($partner) {
            if ($partner->is_active == Partner::IS_NOT_ACTIVE) {
                return ClientResponse::response(ClientResponse::$user_not_active, 'Tài khoản chưa kích hoạt');
            }

            if (!Partner::isCompletedProfile($partner->id ?? 0)) {
                return ClientResponse::response(ClientResponse::$partner_required_fill_info, 'Tài khoản chưa hoàn thiện hồ sơ');
            }
            //
            DB::beginTransaction();
            try {
                //đăng xuất tất cả các tài khoản trên các thiết bị khác
                $this->__logoutOtherDevices($partner->id ?? 0);
                //tạo access token mới
                $access_token = PartnerAccessToken::generateAcessToken($partner->id);
                if ($access_token) {
                    $data = [
                        'user' => [
                            'id' => $partner->id,
                            'name' => $partner->name ?? '',
                            'phone' => $partner->phone,
                        ],
                        'access_token' => $access_token
                    ];
                    DB::commit();
                    return ClientResponse::responseSuccess('Đăng nhập thành công', $data);
                } else {
                    return ClientResponse::responseError('Đã có lỗi xảy ra, vui lòng thử lại sau');
                }
            } catch (\Exception $e) {
                DB::rollBack();
                return ClientResponse::responseError($e->getMessage());
            }
            //
        } else {
            return ClientResponse::responseError('Số điện thoại hoặc mật khẩu không đúng');
        }
    }

    public function checkRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:partners',
            'password' => 'required|string|confirmed|min:6',
        ]);
        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $phone = $request->phone;
        if (!CFunction::isPhoneNumber($phone)) {
            return ClientResponse::responseError('Số điện thoại không đúng định dạng');
        }
        //Tạo, gửi OTP
        $otp = Otp::genOtp();
        $otp_send = Otp::sendOtpToPhone($otp, $phone, Sms::generateRegisterSms($otp));
        if (isset($otp_send['status']) && $otp_send['status'] == 1) {
            return ClientResponse::responseSuccess('Kiểm tha thông tin đăng nhập thành công, chuyển qua màn hình xác nhận OTP');
        } else {
            return ClientResponse::responseError($otp_send['message'] ?? 'Không thể gửi OTP, vui lòng thử lại sau');
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:partners',
            'password' => 'required|string|confirmed|min:6',
            'otp' => 'required',
        ]);
        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $phone = $request->phone;
        $password = $request->password;
        $otp = $request->otp;

        if (!CFunction::isPhoneNumber($phone)) {
            return ClientResponse::responseError('Số điện thoại không đúng định dạng');
        }
        $validate_otp = Otp::validateOtpByPhone($otp, $phone);
        if(isset($validate_otp['status']) && $validate_otp['status']==1){
            $partner = new Partner();
            $partner->phone = $phone;
            $partner->password = Partner::generatePasswordHash($password);
            $partner->is_active = Partner::IS_ACTIVE;
            if($partner->save()){
                //vô hiệu hóa otp
                $otpInfo = Otp::getOtpByPhone($otp, $phone);
                if($otpInfo){
                    $otpInfo->expire_at = time();
                    $otpInfo->save();
                }
                return ClientResponse::responseSuccess('Tạo tài khoản thành công');
            }else{
                return ClientResponse::responseError('Không thể tạo tài khoản, vui lòng thử lại sau');
            }
        }else{
            return ClientResponse::responseError($validate_otp['message']??'OTP không hợp lệ');
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return ClientResponse::responseError('Vui lòng nhập số điện thoại');
        }
        $phone = $request->phone;
        $partner = Partner::getPartnerByPhone($phone);
        if($partner){
            //Tạo, gửi OTP
            $otp = Otp::genOtp();
            $otp_send = Otp::sendOtpToPhone($otp, $phone, Sms::generateForgotPasswordSms($otp));
            if (isset($otp_send['status']) && $otp_send['status'] == 1) {
                return ClientResponse::responseSuccess('Gửi OTP thành công');
            } else {
                return ClientResponse::responseError($otp_send['message'] ?? 'Không thể gửi OTP, vui lòng thử lại sau');
            }
        }else{
            return ClientResponse::responseError('Tài khoản không tồn tại');
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required|string|confirmed|min:6',
            'otp' => 'required',
        ]);
        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $phone = $request->phone;
        $password = $request->password;
        $otp = $request->otp;

        if (!CFunction::isPhoneNumber($phone)) {
            return ClientResponse::responseError('Số điện thoại không đúng định dạng');
        }
        $validate_otp = Otp::validateOtpByPhone($otp, $phone);
        if(isset($validate_otp['status']) && $validate_otp['status']==1){
            $partner = Partner::getPartnerByPhone($phone);
            if($partner){
                $partner->password = Partner::generatePasswordHash($password);
                if($partner->save()){
                    //vô hiệu hóa otp
                    $otpInfo = Otp::getOtpByPhone($otp, $phone);
                    if($otpInfo){
                        $otpInfo->expire_at = time();
                        $otpInfo->save();
                    }
                    return ClientResponse::responseSuccess('Cập nhật mật khẩu thành công');
                }else{
                    return ClientResponse::responseError('Không thể cập nhật mật khẩu, vui lòng thử lại sau');
                }
            }else{
                return ClientResponse::responseError('Tài khoản không tồn tại');
            }
        }else{
            return ClientResponse::responseError($validate_otp['message']??'OTP không hợp lệ');
        }
    }

    public function changePassword(Request $request){
        //TODO,..
        //step 1: validate params, change pass
        //step 2:
    }

    public function changeRefresh(Request $request){
        //TODO,..
    }


    public function logout()
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            if ($tokenInfo->delete()) {
                return ClientResponse::responseSuccess('Đăng xuất thành công');
            } else {
                return ClientResponse::responseError('Đã có lỗi xảy ra, vui lòng thử lại sau');
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function profile()
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                return ClientResponse::responseSuccess('Thông tin tài khoản', $partner);
            } else {
                return ClientResponse::responseError('Tài khoản không tồn tại');
            }
        } else {
            return ClientResponse::responseError('Tài khoản không tồn tại');
        }
    }

    private function __logoutOtherDevices($partner_id)
    {
        PartnerAccessToken::where('partner_id', $partner_id)->delete();
    }
}
