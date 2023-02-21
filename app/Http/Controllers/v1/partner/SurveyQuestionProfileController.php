<?php

namespace App\Http\Controllers\v1\partner;

use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Helpers\FormatDate;
use App\Helpers\RemoveData;
use App\Models\AcademicLevel;
use App\Models\AppSetting;
use App\Models\District;
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
                case QuestionTypeProfile::DISTRICT:
                    $data_response = $value;
                    $data_response['answers'] = District::getAllDistrict();
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
                    $data = $request->all();
                    foreach ($data as $key => $value) {
                        $input[$value['profile_type']] = $value['value_answer'];
                        switch ($value['profile_type']) {
                            case QuestionTypeProfile::FULLNAME:
                                $input[$value['profile_type']] = $value['value_answer'];
                                break;
                            case QuestionTypeProfile::YEAR_OF_BIRTH:
                                $input[$value['profile_type']] = FormatDate::formatDate($value['value_answer']);
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
                            case QuestionTypeProfile::FAMILY_PEOPLE:
                                $input[$value['profile_type']] = (int)$value['value_answer'];
                                break;
                            default:
                                return ClientResponse::responseError('profile type không hợp lệ', $value['profile_type']);
                                break;
                        }
                    }
                    $input['phone'] = $phone;
                    $input['partner_id'] = $partner_id;
                    $partner_profile = PartnerProfile::getDetailPartnerProfile($partner_id);
                    if ($partner_profile) {
                        PartnerProfile::updatePartnerProfile($input, $partner_id);
                        $result =  PartnerProfile::getDetailPartnerProfile($partner_id);
                    } else {
                        $result =  PartnerProfile::create($input);
                    }
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('OK', $result);
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
                    $survey_id = $request->survey_id;
                    $survey_detail = Survey::getDetailSurvey($survey_id);
                    if (!$survey_detail) {
                        return ClientResponse::responseError('Không có Khảo sát phù hợp');
                    }
                    $survey_profile_id = $survey_detail->survey_profile_id;
                    $partner_input_id = $request->partner_input_id ?? 0;
                    $data = $request->all();
                    foreach ($data as $key => $value) {
                        $input[$value['profile_type']] = $value['value_answer'];
                        switch ($value['profile_type']) {
                            case QuestionTypeProfile::FULLNAME:
                                $input[$value['profile_type']] = $value['value_answer'];
                                break;
                            case QuestionTypeProfile::YEAR_OF_BIRTH:
                                $input[$value['profile_type']] = FormatDate::formatDate($value['value_answer']);
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
                            case QuestionTypeProfile::FAMILY_PEOPLE:
                                $input[$value['profile_type']] = (int)$value['value_answer'];
                                break;
                            default:
                                return ClientResponse::responseError('profile type không hợp lệ', $value['profile_type']);
                                break;
                        }
                    }
                    $input['survey_id'] = $survey_id;
                    $input['survey_profile_id'] = $survey_profile_id;
                    $input['partner_input_id'] = $partner_input_id;
                    $result =  SurveyProfileInputs::create($input);
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('OK', $result);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
