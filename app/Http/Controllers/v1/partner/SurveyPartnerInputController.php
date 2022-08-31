<?php

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\Partner;
use App\Models\SurveyPartnerInput;
use Carbon\Carbon;

class SurveyPartnerInputController extends Controller
{

    public function answerSurvey(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try{
                    $validator = Validator::make($request->all(), [
                    ]);
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return ClientResponse::responseError($errorString);
                    }
                    $partner_id = $partner->id??0;
                    $partner_profile = Partner::getPartnerById($partner_id);
                    $input['phone'] = $partner_profile->phone;
                    $input['fullname'] = $partner_profile->name;
                    $input['partner_id'] = $partner_id;
                    $input['survey_id'] = $request->survey_id;
                    $input['state'] = SurveyPartnerInput::STATE_NEW;
                    $input['start_datetime'] =  Carbon::now();
                    $result=SurveyPartnerInput::create($input);
                    // TO do
                    if(!$result){
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Thêm mới thành công', $result);

                }catch (\Exception $ex){
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        }else{
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
       
    }

    public function updateAnswerSurvey(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            try{
                $validator = Validator::make($request->all(), [
                ]);
                if ($validator->fails()) {
                    $errorString = implode(",", $validator->messages()->all());
                    return ClientResponse::responseError($errorString);
                }
                $partner_input_id = $request->partner_input_id;
                $result = SurveyPartnerInput::updateSurveyPartnerInput(['state' =>SurveyPartnerInput::STATE_DONE], $partner_input_id);
                if(!$result){
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
                return ClientResponse::responseSuccess('Cập nhập thành công', $result);
            }catch (\Exception $ex){
                return ClientResponse::responseError($ex->getMessage());
            }
        }else{
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
       
    }
}
