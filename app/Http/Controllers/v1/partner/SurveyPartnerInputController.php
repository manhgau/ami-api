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
use App\Models\SurveyPartner;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyQuestion;
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
                    $input['state'] = SurveyPartnerInput::STATUS_NEW;
                    $input['start_datetime'] =  time();
                    $input['is_answer'] =  $request->option;
                    $survey = Survey::getDetailSurvey($request->survey_id);
                    if (!$survey || $survey->state != Survey::STATUS_ON_PROGRESS) {
                        return ClientResponse::responseError('Khảo sát không tồn tại hoặc đã đóng');
                    }
                    $result = SurveyPartnerInput::create($input);
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
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
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $partner_input_id = $request->partner_input_id;
                    $survey_id = $request->survey_id;
                    $partner_id = $partner->id ?? 0;
                    $input_update['end_datetime'] =   time();
                    $input_update['state'] =  SurveyPartnerInput::STATUS_DONE;
                    $result = SurveyPartnerInput::updateSurveyPartnerInput($input_update, $partner_input_id);
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    $survey_partner = SurveyPartner::checkSurveyPartner($survey_id, $partner_id);
                    SurveyPartner::updateSurveyPartner(['number_of_response_partner' =>  $survey_partner->number_of_response_partner + 1], $partner_input_id);
                    $survey = Survey::getDetailSurvey($survey_id);
                    $count_survey_input = SurveyPartnerInput::countSurveyInput($survey_id, SurveyPartnerInput::ANYNOMOUS_FALSE);
                    if (($count_survey_input < $survey->limmit_of_response) || $survey->limmit_of_response == 0) {
                        $data_survey['number_of_response'] = $survey->number_of_response + 1;
                    } else {
                        $data_survey['state'] = Survey::STATUS_COMPLETED;
                    }
                    Survey::updateSurvey($data_survey, $survey_id);
                    $count_survey_partner_input = SurveyPartnerInput::countSurveyPartnerInput($survey_id, $partner_id);
                    if ($count_survey_partner_input <= $survey->attempts_limit_max && $count_survey_partner_input >= $survey->attempts_limit_min) {
                        $model_profile = PartnerProfile::getDetailPartnerProfile($partner_id);
                        $point = $survey->point;
                        $data['point_tpr'] =  $model_profile->point_tpr + $point;
                        $data['kpi_point_tpr'] = $model_profile->kpi_point_tpr + $point;
                        PartnerProfile::updatePartnerProfile($data, $partner_id);
                        $input_log['partner_id'] = $partner_id;
                        $partner_profile = Partner::getPartnerById($partner_id);
                        $input_log['phone'] = $partner_profile->phone;
                        $input_log['partner_name'] = $partner_profile->name;
                        $input_log['type'] = PartnerPointLog::CONG;
                        $input_log['point'] =  $point;
                        $input_log['action '] = PartnerPointLog::ACTION_FINISHED_ANSWER_SURVEY;
                        $input_log['object_type '] = PartnerPointLog::ACTION_FINISHED_ANSWER_SURVEY;
                        $input_log['object_id '] = $survey_id;
                        PartnerPointLog::create($input_log);
                    }
                    return ClientResponse::responseSuccess('Cập nhập thành công', true);
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
                    $perPage = $request->per_page ?? 10;
                    $page = $request->current_page ?? 1;
                    $status = $request->status;
                    $search = $request->search;
                    $time_now = Carbon::now();
                    $time_end = date('Y-m-d H:i:s', time() - (30 * 86400));
                    $datas = SurveyPartnerInput::getlistSurveyPartnerInput($perPage,  $page, $partner_id, $time_now, $time_end, $search, $status);
                    $datas = RemoveData::removeUnusedData($datas);
                    $array = array();
                    foreach ($datas['data'] as $key => $value) {
                        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $value->end_time)->timestamp;
                        $time_remaining = $timestamp - Carbon::now()->timestamp;
                        $value->time_remaining = floor(max(0, $time_remaining) / (60 * 60 * 24));
                        if ($value->end_time <= $time_now) {
                            $value->status = SurveyPartner::CLOSED;
                        } else {
                            if ($value->number_of_response == $value->limmit_of_response) {
                                $value->status = SurveyPartner::COMPLETED;
                            } else {
                                $value->status = SurveyPartner::NOT_COMPLETED;
                            }
                        }
                        if ($value->is_answer_single == Survey::ANSWER_SINGLE && $value->number_of_response_partner == Survey::ANSWER_SINGLE) {
                            $value->status = SurveyPartner::COMPLETED;
                        }
                        $array[$key] = $value;
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

    public function exitSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $question_id = $request->question_id;
            $partner_input_id = $request->partner_input_id;
            $input_update['end_datetime'] =   time();
            $input_update['skip'] =  SurveyPartnerInput::SKIP;
            $survey = Survey::getDetailSurvey($survey_id);
            $question = SurveyQuestion::getDetailSurveyQuestion($question_id);
            $result = SurveyPartnerInput::updateSurveyPartnerInput($input_update, $partner_input_id);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            Survey::updateSurvey(['skip_count' => $survey->skip_count + 1], $survey_id);
            SurveyQuestion::updateSurveyQuestion(['skip_count' => $question->skip_count + 1], $question_id);
            return ClientResponse::responseSuccess('OK');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
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
                    $partner_profile = $partner->profile;
                    $survey_id = $request->survey_id;
                    $survey_detail = Survey::getDetailSurvey($survey_id);
                    if (!$survey_detail) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    Survey::updateSurvey(['view' => $survey_detail->view + 1], $survey_id);
                    $number_of_response = SurveyPartnerInput::countSurveyInput($survey_id, SurveyPartnerInput::ANYNOMOUS_FALSE);
                    if (($number_of_response >= $survey_detail->limmit_of_response) && $survey_detail->limmit_of_response != 0) {
                        return ClientResponse::response(ClientResponse::$limmit_of_response, 'Khảo sát đã đạt lượt phản hồ giới hạn');
                    }
                    $result = SurveyPartnerInput::checkPartnerInput($partner_id, $survey_id);
                    if (!$result) {
                        $data =  [
                            ['key' => SurveyPartnerInput::PARTNER, 'name' => $partner_profile->fullname, 'disabled' => 0, 'description' => ''],
                            ['key' => SurveyPartnerInput::OTHER, 'name' => 'Khác', 'disabled' => 0, 'description' => '(Thu thập bảng hỏi từ đáp viên khác)'],
                        ];
                        return ClientResponse::responseSuccess('Tài khoản chưa trả lời khảo sát', $data);
                    }
                    $data =  [
                        ['key' => SurveyPartnerInput::PARTNER, 'name' => $partner_profile->fullname, 'disabled' => 1, 'description' => ''],
                        ['key' => SurveyPartnerInput::OTHER, 'name' => 'Khác', 'disabled' => 0, 'description' => '(Thu thập bảng hỏi từ đáp viên khác)'],
                    ];
                    return ClientResponse::responseSuccess('Tài khoản đã trả lời khảo sát', $data);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
