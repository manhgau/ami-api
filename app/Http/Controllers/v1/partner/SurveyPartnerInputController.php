<?php

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\Partner;
use App\Models\PartnerPointLog;
use App\Models\PartnerProfile;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyPartnerInputLine;
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
                    $survey = Survey::getDetailSurvey($request->survey_id);
                    if (!$survey || $survey->state != Survey::STATUS_ON_PROGRESS) {
                        return ClientResponse::responseError('Khảo sát không tồn tại hoặc đã đóng');
                    }
                    $result = SurveyPartnerInput::create($input);
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    Survey::updateSurvey(
                        [
                            "view" => $survey->view + 1,
                        ],
                        $request->survey_id
                    );
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
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $partner_input_id = $request->partner_input_id;
                    $partner_id = $partner->id ?? 0;
                    $input_update['start_datetime'] =  Carbon::now();
                    $input_update['state'] =  SurveyPartnerInput::STATE_DONE;
                    $result = SurveyPartnerInput::updateSurveyPartnerInput($input_update, $partner_input_id);

                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    $survey = Survey::getDetailSurvey($request->survey_id);
                    $count_survey_input = SurveyPartnerInput::countSurveyInput($request->survey_id);
                    if ($count_survey_input == $survey->number_of_response_required) {
                        $input['state'] = Survey::STATUS_COMPLETED;
                        Survey::updateSurvey($input, $request->survey_id);
                    }
                    $count_survey_partner_input = SurveyPartnerInput::countSurveyPartnerInput($request->survey_id, $partner_id);
                    if ($count_survey_partner_input <= $survey->attempts_limit_max && $count_survey_partner_input >= $survey->attempts_limit_min) {
                        $number_input = SurveyPartnerInputLine::countSurveyPartnerInputLine($partner_input_id, $request->survey_id);
                        $point = $number_input * $survey->point;
                        $data['point'] = $point;
                        $data['kpi_point'] = $point;
                        PartnerProfile::updatePartnerProfile($data, $partner_id);
                        $input_log['partner_id'] = $partner_id;
                        $partner_profile = Partner::getPartnerById($partner_id);
                        $input_log['phone'] = $partner_profile->phone;
                        $input_log['partner_name'] = $partner_profile->name;
                        $input_log['type'] = PartnerPointLog::CONG;
                        $input_log['point'] =  $point;
                        $input_log['action '] = PartnerPointLog::ACTION_FINISHED_ANSWER_SURVEY;
                        $input_log['object_type '] = PartnerPointLog::ACTION_FINISHED_ANSWER_SURVEY;
                        $input_log['object_id '] = $request->survey_id;
                        PartnerPointLog::create($input_log);
                    }
                    return ClientResponse::responseSuccess('Cập nhập thành công', $result);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
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
                    $time_end = date('Y-m-d H:i:s', time() - (30 * 86400));
                    $datas = SurveyPartnerInput::getlistSurveyPartnerInput($perPage,  $page, $partner_id, $time_now, $time_end);
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

    public function getDetailSurveyPartnerInput(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $partner_id = $partner->id ?? 0;
                    $survey_partner_input_id = $request->survey_partner_input_id;
                    $result = SurveyPartnerInput::getDetailSurveyPartnerInput($survey_partner_input_id, $partner_id);
                    if (!$result) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    Survey::updateSurvey(['view' => $result->view + 1], $result->survey_id);
                    $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $result->end_time)->timestamp;
                    $time_remaining = $timestamp - Carbon::now()->timestamp;
                    $result = json_decode(json_encode($result), true);
                    $result['time_remaining'] = $time_remaining;
                    return ClientResponse::responseSuccess('OK', $result);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function checkPartnerInput(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $partner_id = $partner->id ?? 0;
                    $result = SurveyPartnerInput::checkPartnerInput($partner_id);
                    if (!$result) {
                        return ClientResponse::responseError('Tài khoản chưa trả lời khảo sát');
                    }
                    return ClientResponse::responseSuccess('Tài khoản đã trả lời khảo sát');
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
