<?php

namespace App\Http\Controllers\v1\partner;
use Illuminate\Http\Request;

use App\Models\Partner;
use App\Models\PartnerAccessToken;
use Validator;
use App\Helpers\ClientResponse;
use DB;
use App\Helpers\JWT;

class AuthController extends Controller
{

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
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

        if($partner){
            if($partner->is_active==Partner::IS_ACTIVE){
                DB::beginTransaction();
                try {
                    //đăng xuất tất cả các tài khoản trên các thiết bị khác
                    $this->__logoutOtherDevices($partner->id??0);
                    //tạo access token mới
                    $access_token = PartnerAccessToken::generateAcessToken($partner->id);
                    if($access_token){
                        $data = [
                            'user'  =>  [
                                'id'        =>  $partner->id,
                                'name'      =>  $partner->name??'',
                                'phone'     =>  $partner->phone,
                            ],
                            'access_token' =>  $access_token
                        ];
                        DB::commit();
                        return ClientResponse::responseSuccess('Đăng nhập thành công', $data);
                    }else{
                        return ClientResponse::responseError('Đã có lỗi xảy ra, vui lòng thử lại sau');
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    return ClientResponse::responseError($e->getMessage());
                }
            }else{
                return ClientResponse::response(ClientResponse::$user_not_active, 'Tài khoản chưa kích hoạt');
            }
        }else{
            return ClientResponse::responseError('Số điện thoại hoặc mật khẩu không đúng');
        }
    }

    public function logout(){
        $token = request()->header('Authorization');
        $access_token = JWT::checkAccessToken($token);
        if($access_token){
            $aid = $access_token->aid??0;
            $tokenInfo = PartnerAccessToken::where('aid',$aid)->first();
            if($tokenInfo){
                if($tokenInfo->delete()){
                    return ClientResponse::responseSuccess('Đăng xuất thành công');
                }else{
                    return ClientResponse::responseError('Đã có lỗi xảy ra, vui lòng thử lại sau');
                }
            }else{
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        }else{
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function profile(){
        $partner  = Partner::getPartnerFromAccessToken();
        if($partner){
            return ClientResponse::responseSuccess('Thông tin tài khoản',$partner);
        }else{
            return ClientResponse::responseError('Tài khoản không tồn tại');
        }
    }

    private function __logoutOtherDevices($partner_id){
        PartnerAccessToken::where('partner_id',$partner_id)->delete();
    }
}
