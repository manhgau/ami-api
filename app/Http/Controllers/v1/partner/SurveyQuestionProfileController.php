<?php

namespace App\Http\Controllers\v1\partner;

use App\Helpers\ClientResponse;
use App\Helpers\RemoveData;
use App\Models\AcademicLevel;
use App\Models\FamilyIncomeLevels;
use App\Models\Gender;
use App\Models\JobType;
use App\Models\MaritalStatus;
use App\Models\PersonalIncomeLevels;
use App\Models\Province;
use App\Models\QuestionTypeProfile;
use App\Models\SurveyProfileQuestions;
use Illuminate\Http\Request;

class SurveyQuestionProfileController extends Controller
{

    public function getSurveyQuestionProfile(Request $request)
    {

        try {
            $survey_profile_id = $request->survey_profile_id;
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $lists = SurveyProfileQuestions::getSurveyQuestionProfile($survey_profile_id, $perPage, $page);
            $lists = RemoveData::removeUnusedData($lists);
            if (!$lists) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $datas = [];
            foreach ($lists['data'] as  $value) {
                switch ($value['profile_type']) {
                    case QuestionTypeProfile::FULLNAME:
                    case QuestionTypeProfile::FAMILY_PEOPLE:
                    case QuestionTypeProfile::YEAR_OF_BIRTH:
                    case QuestionTypeProfile::IS_KEY_SHOPPER:
                    case QuestionTypeProfile::HAS_CHILDREN:
                        $data_response = $value;
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
            return ClientResponse::responseSuccess('OK', $lists);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
