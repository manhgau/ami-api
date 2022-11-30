<?php

namespace App\Http\Controllers\v1\partner;

use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyPartner;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use Illuminate\Http\Request;

class SurveyQuestionPartnerController extends Controller
{

    public function getSurveyQuestion(Request $request)
    {

        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $survey_id = $request->survey_id;
                    $perPage = $request->per_page ?? 20;
                    $page = $request->current_page ?? 1;
                    $partner_id = $partner->id ?? 0;
                    if (!SurveyPartner::checkSurveyPartner($survey_id, $partner_id)) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    $survey_setup = Survey::getSetupSurvey($survey_id);
                    $lists = SurveyQuestion::getListQuestion($survey_id, $perPage, $page,  $survey_setup->is_random);
                    $lists = RemoveData::removeUnusedData($lists);
                    if (!$lists) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    $datas = [];
                    foreach ($lists['data'] as $key => $value) {
                        if ($value['question_type'] == QuestionType::GROUP) {

                            $question_group = SurveyQuestion::listGroupQuestions($survey_id, $value['id']);
                            $list_question = [];
                            foreach ($question_group as $cat => $item) {
                                $list_question  = self::__getAnswer($cat, $item, $list_question);
                            }
                            $value['group_question'] = $list_question;
                            $datas[$key] = $value;
                        } else {
                            $datas  = self::__getAnswer($key, $value, $datas);
                        }
                    }
                    $lists['data'] = $datas;
                    return ClientResponse::responseSuccess('OK', $lists);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            } else {
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        }
    }

    private static function __getAnswer($key, $value, $datas)
    {
        $question_id = $value['id'];
        $random = $value['validation_random'];
        switch ($value['question_type']) { // question_id 
            case QuestionType::MULTI_FACTOR_MATRIX:
                $data_response = $value;
                $data_response['answers'] = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id, $random)->orWhere('matrix_question_id', $value['id'])->get();
                $datas[$key] = $data_response;
                break;
            case QuestionType::MULTI_CHOICE:
            case QuestionType::MULTI_CHOICE_DROPDOWN:
            case QuestionType::YES_NO:
                $data_response = $value;
                $data_response['answers'] = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id, $random)->get();
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
                $datas[$key] = $value;
                break;
            default:
                return ClientResponse::responseError('question type không hợp lệ', $value['question_type']);
                break;
        }
        return $datas;
    }
}
