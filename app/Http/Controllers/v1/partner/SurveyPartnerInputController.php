<?php

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\Partner;
use App\Models\Survey;
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
                try {
                    $validator = Validator::make($request->all(), []);
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return ClientResponse::responseError($errorString);
                    }
                    $survey = Survey::getDetailSurvey($request->survey_id);
                    if ($survey->state == Survey::STATUS_COMPLETED) {
                        return ClientResponse::responseError('Khảo sát đã đóng');
                    }
                    $partner_id = $partner->id ?? 0;
                    $partner_profile = Partner::getPartnerById($partner_id);
                    $input['phone'] = $partner_profile->phone;
                    $input['fullname'] = $partner_profile->name;
                    $input['partner_id'] = $partner_id;
                    $input['survey_id'] = $request->survey_id;
                    $input['state'] = SurveyPartnerInput::STATE_NEW;
                    $input['start_datetime'] =  Carbon::now();
                    $result = SurveyPartnerInput::create($input);
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    $count = SurveyPartnerInput::countSurveyPartnerInput($result->survey_id);
                    if ($count == $survey->number_of_response_required) {
                        Survey::updateSurvey(['state' => Survey::STATUS_COMPLETED], $request->survey_id);
                    }
                    return ClientResponse::responseSuccess('Thêm mới thành công', $result);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function updateAnswerSurvey(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            try {
                $validator = Validator::make($request->all(), []);
                if ($validator->fails()) {
                    $errorString = implode(",", $validator->messages()->all());
                    return ClientResponse::responseError($errorString);
                }
                $partner_input_id = $request->partner_input_id;
                $result = SurveyPartnerInput::updateSurveyPartnerInput(['state' => SurveyPartnerInput::STATE_DONE], $partner_input_id);
                if (!$result) {
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
                $survey = Survey::getDetailSurvey($request->survey_id);
                $input['number_of_response'] = $survey->number_of_response + 1;
                if ($input['number_of_response']  == $survey->number_of_response_required) {
                    $input['state'] = Survey::STATUS_COMPLETED;
                    Survey::updateSurvey($input, $request->survey_id);
                }
                Survey::updateSurvey($input, $request->survey_id);
                return ClientResponse::responseSuccess('Cập nhập thành công', $result);
            } catch (\Exception $ex) {
                return ClientResponse::responseError($ex->getMessage());
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function getlistSurveyPartnerInput(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $partner_id = $partner->id ?? 0;
                    $perPage = $request->per_page ?? 5;
                    $page = $request->current_page ?? 1;
                    $time_now = Carbon::now();
                    $datas = SurveyPartnerInput::getlistSurveyPartnerInput($perPage,  $page, $partner_id, $time_now);
                    $datas = RemoveData::removeUnusedData($datas);
                    $array = array();
                    foreach ($datas['data'] as $key => $value) {
                        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $value->end_time)->timestamp;
                        $time_remaining = $timestamp - Carbon::now()->timestamp;
                        $data = json_decode(json_encode($value), true);
                        $data['time_remaining'] = $time_remaining;
                        $array[$key] = $data;
                    }
                    $datas['data'] = $array;
                    if (!$datas) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    return ClientResponse::responseSuccess('OK', $datas);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
