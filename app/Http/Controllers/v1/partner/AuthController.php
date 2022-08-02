<?php

namespace App\Http\Controllers\v1\partner;

use App\Models\PartnerProfile;
use Illuminate\Http\Request;

use App\Models\Partner;
use App\Models\Otp;
use App\Models\Sms;
use App\Models\PartnerAccessToken;
use App\Models\PartnerChildrenAgeRange;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CFunction;
use DB;
use App\Helpers\Context;
use App\Helpers\JWT;

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
            //
            DB::beginTransaction();
            try {
                //đăng xuất tất cả các tài khoản trên các thiết bị khác
                $this->__logoutOtherDevices($partner->id ?? 0);
                //tạo access và refresh token mới
                $token = PartnerAccessToken::generateAccessRefreshToken($partner->id);
                if ($token) {
                    $data = [
                        'user' => [
                            'id' => $partner->id,
                            'name' => $partner->name ?? '',
                            'phone' => $partner->phone,
                        ],
                        'access_token'  => $token['access_token']??'',
                        'refresh_token' => $token['refresh_token']??'',
                    ];
                    DB::commit();
                    if (Partner::isCompletedProfile($partner??null)) {
                        return ClientResponse::responseSuccess('Đăng nhập thành công', $data);
                    }else{
                        return ClientResponse::response(ClientResponse::$partner_required_fill_info, 'Tài khoản chưa hoàn thiện hồ sơ', $data);
                    }
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
        $otp = Otp::genOtp($phone);
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
            $otp = Otp::genOtp($phone);
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
        try{
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|confirmed|min:6',
            ]);

            if($validator->fails()){
                $errorString = implode(",",$validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
            if ($tokenInfo) {
                $partner = $tokenInfo->partner;
                if($partner){
                    $partner->password = Partner::generatePasswordHash($request->new_password);
                    if($partner->save()){
                        //xóa token cũ
                        $tokenInfo->delete();
                        return ClientResponse::responseSuccess('Đổi mật khẩu thành công');
                    }else{
                        return ClientResponse::responseError('Không thể đổi mật khẩu');
                    }
                }else{
                    return ClientResponse::responseError('Tài khoản không tồn tại');
                }
            } else {
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        }catch (\Exception $ex){
            return ClientResponse::responseError($ex->getMessage());
        }

    }


    public function refresh(Request $request){
        $token = $request->header('Authorization');
        $refresh_token = JWT::checkAccessToken($token);
        if($refresh_token){
            $access_token_id = $refresh_token->aid??0;
            $type = $refresh_token->type??'';
            $tokenInfo = PartnerAccessToken::where('aid',$access_token_id)->first();
            if ($tokenInfo && $type==PartnerAccessToken::TYPE_REFRESH_TOKEN) {
                $partner = $tokenInfo->partner;
                if($partner){
                    $time = time();

                    $refresh_token_expire = $tokenInfo->refresh_expire ?? 0;
                    if ($refresh_token_expire >= $time) {
                        //
                        DB::beginTransaction();
                        try {
                            //đăng xuất tất cả các tài khoản trên các thiết bị khác
                            $this->__logoutOtherDevices($partner->id ?? 0);
                            //tạo access và refresh token mới
                            $token = PartnerAccessToken::generateAccessRefreshToken($partner->id);
                            if ($token) {
                                $data = [
                                    /*'user' => [
                                        'id' => $partner->id,
                                        'name' => $partner->name ?? '',
                                        'phone' => $partner->phone,
                                    ],*/
                                    'access_token'  => $token['access_token']??'',
                                    'refresh_token' => $token['refresh_token']??'',
                                ];
                                DB::commit();
                                return ClientResponse::responseSuccess('Refresh thành công', $data);
                            } else {
                                return ClientResponse::responseError('Đã có lỗi xảy ra, vui lòng thử lại sau');
                            }
                        } catch (\Exception $e) {
                            DB::rollBack();
                            return ClientResponse::responseError($e->getMessage());
                        }
                    }else{
                        return ClientResponse::response(ClientResponse::$required_login_code, 'Refresh token đã hết hạn');
                    }
                }else{
                    return ClientResponse::responseError('Tài khoản không tồn tại');
                }
            } else {
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        }else{
            return ClientResponse::response(ClientResponse::$required_login_code, 'Yêu cầu truy cập bị từ chối');
        }
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
                return ClientResponse::responseSuccess('Thông tin tài khoản', [
                    'user'      =>  $partner,
                    'profile'   =>  $partner->profile
                ]);
            } else {
                return ClientResponse::responseError('Tài khoản không tồn tại');
            }
        } else {
            return ClientResponse::responseError('Tài khoản không tồn tại');
        }
    }

    public function updateProfile(Request $request){
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try{
                    $partner_id = $partner->id??0;
                    $validator = Validator::make($request->all(), [
                        //required
                        'year_of_birth' => 'required|digits:4|integer|min:1900|max:'.(date('Y')+1),
                        'gender'        => 'required|digits:1|integer|exists:App\Models\Gender,id',
                        'province_code' => 'required|string|exists:App\Models\Province,code',
                        'district_code' => 'required|string|exists:App\Models\District,code',
                        'ward_code' => 'required|string|exists:App\Models\Ward,code',
                        'job_type_id'   => 'required|integer|exists:App\Models\JobType,id',
                        'academic_level_id' => 'required|integer|exists:App\Models\AcademicLevel,id',
                        'marital_status_id' => 'integer|exists:App\Models\MaritalStatus,id',
                        //
                        'job_status_id' => 'required|integer|exists:App\Models\JobStatus,id',
                        'personal_income_level_id' => 'integer|exists:App\Models\PersonalIncomeLevels,id',
                        'family_income_level_id' => 'integer|exists:App\Models\PersonalIncomeLevels,id',
                        'family_people' => 'integer',
                        'is_key_shopper' => 'boolean',
                        'has_children' => 'boolean',
                        'most_cost_of_living' => 'boolean',
                        'childrend_age_ranges' => 'array',
                        'childrend_age_ranges.*' => 'exists:App\Models\ChildrendAgeRanges,id', // check each item in the array

                    ]);

                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return ClientResponse::responseError($errorString);
                    }
                    //$input = $validator->valid();

                    $profile = $partner->profile;
                    if(!$profile) {
                        $profile = new PartnerProfile();
                        $profile->partner_id = $partner_id;
                    }
                    //required
                    $profile->year_of_birth = $request->year_of_birth;
                    $profile->gender = $request->gender;
                    $profile->province_code = $request->province_code;
                    $profile->district_code = $request->district_code;
                    $profile->ward_code = $request->ward_code;
                    $profile->job_type_id = $request->job_type_id;
                    $profile->job_status_id = $request->job_status_id;
                    $profile->academic_level_id = $request->academic_level_id;
                    $profile->marital_status_id = $request->marital_status_id;

                    $profile->personal_income_level_id = $request->personal_income_level_id;
                    $profile->family_income_level_id = $request->family_income_level_id;
                    $profile->family_people = $request->family_people;
                    $profile->is_key_shopper = $request->is_key_shopper;
                    $profile->has_children = $request->has_children;
                    $profile->most_cost_of_living = $request->most_cost_of_living;
                    //update profile
                    $profile->save();
                    //update childrend_age_ranges
                    $childrend_age_ranges = $request->childrend_age_ranges;
                    if(is_array($childrend_age_ranges) && count($childrend_age_ranges) > 0){
                        PartnerChildrenAgeRange::where('partner_id', $partner_id)->delete();
                        foreach ($childrend_age_ranges as $cr) {
                            $m = new PartnerChildrenAgeRange();
                            $m->partner_id = $partner_id;
                            $m->childrend_age_range_id = $cr;
                            $m->save();
                        }
                    }

                    return ClientResponse::responseSuccess('Cập nhật thông tin tài khoản thành công');

                }catch (\Exception $ex){
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        }else{
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    private function __logoutOtherDevices($partner_id)
    {
        PartnerAccessToken::where('partner_id', $partner_id)->delete();
    }
}
