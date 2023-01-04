<?php

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\MappingUidFcmToken;
use Illuminate\Support\Str;

class MappingUidFcmTokenController extends Controller
{

    public function mappingUidFcmToken(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string|max:254',
            'os' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        $os = Str::lower($request->os);
        if (in_array($os, MappingUidFcmToken::getOSList()) == false) {
            return ClientResponse::responseError('Nhập sai os');
        }
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            $partner_id =  $partner->id ?? 0;
            $input['device_id'] = $request->header('DeviceId');
            $input['fcm_token'] = $request->fcm_token;
            $input['os'] = $request->os;
            $partner = MappingUidFcmToken::getMappingUidFcmTokenByPartnerId($partner_id);
            if ($partner) {
                $update = MappingUidFcmToken::where('partner_id', $partner_id)->update($input);
                if ($update) {
                    return ClientResponse::responseSuccess('Câp nhập thành công');
                }
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $input['partner_id'] = $partner_id;
            $create = MappingUidFcmToken::create($input);
            return ClientResponse::responseSuccess('Thêm mới thành công', $create);
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
    public function checkFcmToken(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            $partner_id =  $partner->id ?? 0;
            $device_id = $request->header('DeviceId');
            $check_fcm_token = MappingUidFcmToken::checkFcmToken($partner_id, $device_id);
            if (!$check_fcm_token) {
                return ClientResponse::response(ClientResponse::$check_partner_mapping_fcmtoken, 'Tài khoản chưa cập nhập FcmToken');
            }
            return ClientResponse::responseSuccess('Tài khoản đã cập nhập FcmToken', $check_fcm_token);
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
