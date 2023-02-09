<?php

namespace App\Http\Controllers\v1\client;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\RemoveData;
use App\Models\AppSetting;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use Carbon\Carbon;
use Jenssegers\Agent\Facades\Agent;



class SurveyPartnerInputAnynomousController extends Controller
{

    public function answerSurveyAnynomous(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), []);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $input['survey_id'] = $request->survey_id;
            $input['state'] = SurveyPartnerInput::STATUS_NEW;
            $input['start_datetime'] =  time();
            $input['os'] = Agent::device();
            $input['ip'] = $request->ip();
            $input['browser'] = Agent::browser();
            $input['user_agent'] = $request->server('HTTP_USER_AGENT');
            $input['is_anynomous'] = SurveyPartnerInput::ANYNOMOUS_TRUE;
            $survey = Survey::getDetailSurvey($request->survey_id);
            if (!$survey || $survey->state != Survey::STATUS_ON_PROGRESS) {
                return ClientResponse::responseError('Khảo sát không tồn tại hoặc đã đóng');
            }
            $result = SurveyPartnerInput::create($input);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            Survey::updateSurvey(['view' => $survey->view + 1], $request->survey_id);
            return ClientResponse::responseSuccess('Thêm mới thành công', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function updateAnswerSurveyAnynomous(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $survey = Survey::getDetailSurvey($survey_id);
            if (!$survey) {
                return ClientResponse::responseError('Không tồn tại khảo sát này');
            }
            $partner_input_id = $request->partner_input_id;
            $input_update['end_datetime'] =   time();
            $input_update['state'] =  SurveyPartnerInput::STATUS_DONE;
            $result = SurveyPartnerInput::updateSurveyPartnerInput($input_update, $partner_input_id);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $count_survey_input = SurveyPartnerInput::countSurveyInput($survey_id, SurveyPartnerInput::ANYNOMOUS_TRUE);
            if ($survey->limmit_of_response_anomyous != 0 & ($count_survey_input >= $survey->limmit_of_response_anomyous)) {
                $data_survey['state'] = Survey::STATUS_COMPLETED;
                Survey::updateSurvey($data_survey, $survey_id);
            }
            return ClientResponse::responseSuccess('Cập nhập thành công', true);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }


    public function getSurveyQuestion(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $perPage = $request->per_page ?? 20;
            $page = $request->current_page ?? 1;
            $survey_setup = Survey::getSetupSurvey($survey_id);
            if (!$survey_setup) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $list_answer_logic = SurveyQuestionAnswer::getLogicAnswer($survey_id);
            $logic_comes = [];
            if (is_array($list_answer_logic) && count($list_answer_logic) > 0) {
                foreach ($list_answer_logic as $key => $value) {
                    $logic_comes[$key] = $value['logic_come'];
                }
            }
            $lists = SurveyQuestion::getListQuestion($survey_id, $perPage, $page,  $survey_setup->is_random,  $logic_comes);
            $lists = RemoveData::removeUnusedData($lists);
            if (!$lists) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $datas = [];
            $all_settings = AppSetting::getAllSetting();
            $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
            foreach ($lists['data'] as $key => $value) {
                $value->background ? $value->background = $image_domain . $value->background : null;
                if ($value->question_type == QuestionType::GROUP) {

                    $question_group = SurveyQuestion::listGroupQuestions($survey_id, $value->id, $logic_comes);
                    $list_question = [];
                    foreach ($question_group as $cat => $item) {
                        $item->background ? $item->background = $image_domain . $item->background : null;
                        $list_question  = self::__getAnswer($cat, $item, $list_question);
                    }
                    $value->group_question = $list_question;
                    $datas[$key] = $value;
                } else {
                    $datas  = self::__getAnswer($key, $value, $datas);
                }
            }
            $lists['data'] = $datas;
            $survey_setup->background ? $survey_setup->background = $image_domain . $survey_setup->background : null;
            $lists['survey_setup'] = $survey_setup;
            Survey::updateSurvey(['view' => $survey_setup->view + 1], $survey_id);
            return ClientResponse::responseSuccess('OK', $lists);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    private static function __getAnswer($key, $value, $datas)
    {
        $question_id = $value->id;
        $random = $value->validation_random;
        $data_response = $value;
        switch ($value->question_type) { // question_id 
            case QuestionType::MULTI_FACTOR_MATRIX:
                $data_response = $value;
                $data_response->answers = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id, $random)->get();
                $datas[$key] = $data_response;
                break;
            case QuestionType::MULTI_CHOICE:
            case QuestionType::MULTI_CHOICE_DROPDOWN:
            case QuestionType::YES_NO:
                $data_response = $value;
                $data_response->answers = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id, $random)->get();
                $datas[$key] = $data_response;
                break;
            case QuestionType::RATING_STAR:
            case QuestionType::RANKING:
            case QuestionType::DATETIME_DATE:
            case QuestionType::DATETIME_DATE_RANGE:
            case QuestionType::QUESTION_ENDED_SHORT_TEXT:
            case QuestionType::QUESTION_ENDED_LONG_TEXT:
            case QuestionType::NUMBER:
            case QuestionType::GROUP:
                $datas[$key] = $data_response;
                break;
            default:
                return ClientResponse::responseError('question type không hợp lệ', $value->question_type);
                break;
        }
        return $datas;
    }
}
