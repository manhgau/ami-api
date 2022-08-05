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

    public function MappingUidFcmToken(Request $request)
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

        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            $partner_id =  $partner->id??0;
            $partner = MappingUidFcmToken::getMappingUidFcmTokenByPartnerId($partner_id);
            if ($partner) {
                $update = MappingUidFcmToken::where('partner_id', $partner_id)->update(
                    ['fcm_token' => $request->fcm_token,'os'=>$os]
                );
                if ($update) {
                    return ClientResponse::responseSuccess('Câp nhập thành công');
                }
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $create = MappingUidFcmToken::create(['partner_id' => $partner_id, 'fcm_token' => $request->fcm_token, 'os'=>$os]);
            return ClientResponse::responseSuccess('Thêm mới thành công', $create);
    }
}
}
