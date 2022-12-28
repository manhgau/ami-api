<?php

namespace App\Http\Controllers\v1\partner;

use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Helpers\FormatDate;
use App\Helpers\RemoveData;
use App\Models\AcademicLevel;
use App\Models\AppSetting;
use App\Models\FamilyIncomeLevels;
use App\Models\Gender;
use App\Models\JobType;
use App\Models\MaritalStatus;
use App\Models\PartnerProfile;
use App\Models\PersonalIncomeLevels;
use App\Models\Province;
use App\Models\QuestionTypeProfile;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyProfileInputs;
use App\Models\SurveyProfileQuestions;
use Illuminate\Http\Request;
use Validator;

class SurveyQuestionProfileController extends Controller
{

    private function __questionProfile($survey_profile_id, $perPage, $page)
    {
        $lists = SurveyProfileQuestions::getSurveyQuestionProfile($survey_profile_id, $perPage, $page);
        $lists = RemoveData::removeUnusedData($lists);
        if (count($lists['data']) == 0) {
            return null;
        }
        $datas = [];
        foreach ($lists['data'] as  $value) {
            switch ($value['profile_type']) {
                case QuestionTypeProfile::FULLNAME:
                case QuestionTypeProfile::FAMILY_PEOPLE:
                case QuestionTypeProfile::YEAR_OF_BIRTH:
                    $data_response = $value;
                    $datas[] = $data_response;
                    break;
                case QuestionTypeProfile::IS_KEY_SHOPPER:
                case QuestionTypeProfile::HAS_CHILDREN:
                    $data_response = $value;
                    $data_response['answers'] = [
                        ["value" => 1, "name" => "Có"],
                        ["value" => 2, "name" => "Không"],
                    ];
                    $datas[] = $data_response;
                    break;
                case QuestionTypeProfile::PROVINCE:
                    $data_response = $value;
                    $data_response['answers'] = Province::getAllProvince();
                    $datas[] = $data_response;
                    break;
                case QuestionTypeProfile::GENDER:
                    $data_response = $value;
                    $data_response['answers'] = Gender::getAllGender();
                    $datas[] = $data_response;
                    break;
                case QuestionTypeProfile::MARITAL_STATUS:
                    $data_response = $value;
                    $data_response['answers'] = MaritalStatus::getAllMaritalStatus();
                    $datas[] = $data_response;
                    break;
                case QuestionTypeProfile::JOB_TYPE:
                    $data_response = $value;
                    $data_response['answers'] = JobType::getAllJobType();
                    $datas[] = $data_response;
                    break;
                case QuestionTypeProfile::PERSONAL_INCOME_LEVEL:
                    $data_response = $value;
                    $data_response['answers'] = PersonalIncomeLevels::getAllPersonalIncomeLevels();
                    $datas[] = $data_response;
                    break;
                case QuestionTypeProfile::FAMILY_INCOME_LEVEL:
                    $data_response = $value;
                    $data_response['answers'] = FamilyIncomeLevels::getAllFamilyIncomeLevels();
                    $datas[] = $data_response;
                    break;
                case QuestionTypeProfile::ACADEMIC_LEVEL:
                    $data_response = $value;
                    $data_response['answers'] = AcademicLevel::getAllAcademicLevel();
                    $datas[] = $data_response;
                    break;
                default:
                    return ClientResponse::responseError('profile type không hợp lệ', $value['profile_type']);
                    break;
            }
        }
        $lists['data'] = $datas;
        return  $lists;
    }
    public function getSurveyQuestionProfile(Request $request)
    {

        try {
            $survey_profile_id = $request->survey_profile_id;
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $lists = $this->__questionProfile($survey_profile_id, $perPage, $page);
            return ClientResponse::responseSuccess('OK', $lists);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getQuestionProfileBySurvey(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $survey_id = $request->survey_id;
                    $option = $request->option ?? 0;
                    $partner_id = $partner->id ?? 0;
                    $survey_detail = Survey::getDetailSurvey($survey_id);
                    if (!$survey_detail) {
                        return ClientResponse::responseError('Không có Khảo sát phù hợp');
                    }
                    $survey_profile_id = $survey_detail->survey_profile_id;
                    if (!$survey_profile_id) {
                        return ClientResponse::responseSuccess('OK', null);
                    }
                    if ($option == SurveyPartnerInput::PARTNER) {
                        $input = PartnerProfile::getPartnerProfileDetail($partner_id);
                        $input['partner_id'] = $partner_id;
                        $input['survey_id'] = $survey_id;
                        $input['survey_profile_id'] = $survey_profile_id;
                        $model = SurveyProfileInputs::getSurveyProfileInputDetail($survey_profile_id, $partner_input_id = null, $survey_id, $partner_id);
                        if ($model) {
                            $result = $model->update($input);
                        } else {
                            $result = SurveyProfileInputs::create($input);
                        }
                        if (!$result) {
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        return ClientResponse::responseSuccess('OK', null);
                    }
                    $perPage = $request->per_page ?? 10;
                    $page = $request->current_page ?? 1;
                    $lists = $this->__questionProfile($survey_profile_id, $perPage, $page);
                    return ClientResponse::responseSuccess('OK', $lists);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            } else {
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        }
    }

    public function answerSurveyQuestionProfile(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $partner_id = $partner->id ?? 0;
                    $phone = $partner->phone;
                    $survey_profile_id = $request->survey_profile_id;
                    $partner_input_id = $request->partner_input_id ?? 0;
                    $question_id = $request->question_id;
                    $profile_type = $request->profile_type;
                    $value_answer = $request->value_answer;
                    $is_partner_profile = 1;
                    $result = $this->__answerQuestionProfile($survey_profile_id, $partner_input_id, $question_id, $profile_type,  $value_answer, $partner_id, $survey_id = null, $is_partner_profile, $phone);
                    if ($result['code'] == false) {
                        return ClientResponse::response(ClientResponse::$validator_value, $result['status']);
                    }
                    return ClientResponse::responseSuccess('OK', $result['status']);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function answerQuestionProfileBySurvey(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $phone = $partner->phone;
                    $survey_id = $request->survey_id;
                    $survey_detail = Survey::getDetailSurvey($survey_id);
                    if (!$survey_detail) {
                        return ClientResponse::responseError('Không có Khảo sát phù hợp');
                    }
                    $survey_profile_id = $survey_detail->survey_profile_id;
                    $partner_input_id = $request->partner_input_id ?? 0;
                    $question_id = $request->question_id;
                    $profile_type = $request->profile_type;
                    $value_answer = $request->value_answer;
                    $is_partner_profile = 0;
                    $result = $this->__answerQuestionProfile($survey_profile_id, $partner_input_id,  $question_id, $profile_type,  $value_answer, $partner_id = null, $survey_id, $is_partner_profile, $phone);
                    if ($result['code'] == false) {
                        return ClientResponse::response(ClientResponse::$validator_value, $result['status']);
                    }
                    return ClientResponse::responseSuccess('OK', $result['status']);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    private function __answerQuestionProfile($survey_profile_id, $partner_input_id, $question_id, $profile_type,  $value_answer, $partner_id, $survey_id, $is_partner_profile, $phone)
    {
        //$is_partner_profile kiểm tra xem có update profile ko
        try {
            $question_detail = SurveyProfileQuestions::getSurveyQuestionProfileDetail($survey_profile_id, $question_id);
            if (!$question_detail) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            switch ($question_detail->profile_type) {
                case QuestionTypeProfile::FULLNAME:
                    $validator = Validator::make(
                        [
                            'value_answer' => $value_answer,
                            'profile_type' => $profile_type
                        ],
                        [
                            'value_answer' => [
                                $question_detail->validation_required ? 'required' : '',
                                'string',
                            ],
                        ],
                        [
                            'value_answer.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                        ]
                    );
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return [
                            'code' => false,
                            'status' =>  $errorString,
                        ];
                    }
                    $input[$profile_type] = $value_answer;
                    break;
                case QuestionTypeProfile::FAMILY_PEOPLE:
                    $validator = Validator::make(
                        [
                            'value_answer' => $value_answer,
                            'profile_type' => $profile_type
                        ],
                        [
                            'value_answer' => [
                                $question_detail->validation_required ? 'required' : '',
                                'integer',
                            ],
                        ],
                        [
                            'value_answer.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                            'value_answer.integer' => 'Câu hỏi nhận dữ liệu kiểu số.', // custom message     
                        ]
                    );
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return [
                            'code' => false,
                            'status' =>  $errorString,
                        ];
                    }
                    $input[$profile_type] = $value_answer;
                    break;
                case QuestionTypeProfile::YEAR_OF_BIRTH:
                    $validator = Validator::make(
                        [
                            'value_answer' => $value_answer,
                            'profile_type' => $profile_type
                        ],
                        [
                            'value_answer' => [
                                $question_detail->validation_required ? 'required' : '',
                                'date',
                            ],
                        ],
                        [
                            'value_answer.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                        ]
                    );
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return [
                            'code' => false,
                            'status' =>  $errorString,
                        ];
                    }
                    $input[$profile_type] = FormatDate::formatDate($value_answer);
                    break;
                case QuestionTypeProfile::IS_KEY_SHOPPER:
                case QuestionTypeProfile::HAS_CHILDREN:
                case QuestionTypeProfile::PROVINCE:
                case QuestionTypeProfile::GENDER:
                case QuestionTypeProfile::MARITAL_STATUS:
                case QuestionTypeProfile::JOB_TYPE:
                case QuestionTypeProfile::PERSONAL_INCOME_LEVEL:
                case QuestionTypeProfile::FAMILY_INCOME_LEVEL:
                case QuestionTypeProfile::ACADEMIC_LEVEL:
                    $validator = Validator::make(
                        [
                            'value_answer' => $value_answer,
                            'profile_type' => $profile_type
                        ],
                        [
                            'value_answer' => [
                                $question_detail->validation_required ? 'required' : '',
                            ],
                        ],
                        [
                            'value_answer.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                        ]
                    );
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return [
                            'code' => false,
                            'status' =>  $errorString,
                        ];
                    }
                    $input[$profile_type] = $value_answer;
                    break;
                default:
                    return ClientResponse::responseError('profile type không hợp lệ', $question_detail->profile_type);
                    break;
            }
            $input['survey_profile_id'] = $survey_profile_id;
            $input['partner_input_id'] = (int)$partner_input_id;
            $input['partner_id'] = $partner_id;
            $input['phone'] = $phone;
            $input['survey_id'] = $survey_id;
            $model = SurveyProfileInputs::getSurveyProfileInputDetail($survey_profile_id, $survey_id, $partner_input_id, $partner_id);
            $model_profile = PartnerProfile::getDetailPartnerProfile($partner_id);
            if ($is_partner_profile == 1) {
                if ($model_profile) {
                    $result = $model_profile->update($input);
                } else {
                    $result =  PartnerProfile::create($input);
                }
            } else {
                if ($model) {
                    $result = $model->update($input);
                } else {
                    $result = SurveyProfileInputs::create($input);
                }
            }
            return [
                'code' => false,
                'status' =>  $result,
            ];
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
