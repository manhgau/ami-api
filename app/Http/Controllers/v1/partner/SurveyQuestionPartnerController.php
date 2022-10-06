<?php

namespace App\Http\Controllers\v1\partner;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\Context;
use App\Models\QuestionType;
use App\Models\SurveyPartnerInput;
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
                    $partner_id = $partner->id ?? 0;
                    $survey_id = $request->survey_id;
                    $ckey  = CommonCached::cache_find_survey_question_by_survey_id . "_" . $survey_id;
                    $datas = CommonCached::getData($ckey);
                    if (empty($datas)) {
                        $list = SurveyQuestion::getListSurveyQuestion($survey_id);
                        if (!$list) {
                            return ClientResponse::responseError('Không có bản ghi phù hợp');
                        }
                        $datas = [];
                        foreach ($list as $key => $value) {
                            $question_id = $value['id'];
                            switch ($value['question_type']) { // question_id 
                                case QuestionType::MULTI_FACTOR_MATRIX:
                                    $data_response = $value;
                                    $data_response['answers'] = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id)->orWhere('matrix_question_id', $value['id'])->get();
                                    $data_response['response'] = SurveyPartnerInput::getALLSurveyPartnerInput($survey_id, $question_id, $partner_id)
                                        ->select('survey_partner_input_lines.matrix_row_id', 'survey_partner_input_lines.matrix_column_id')->get();
                                    $datas[$key] = $data_response;
                                    break;
                                case QuestionType::MULTI_CHOICE_CHECKBOX:
                                case QuestionType::MULTI_CHOICE_RADIO:
                                case QuestionType::MULTI_CHOICE_DROPDOWN:
                                case QuestionType::YES_NO:
                                    $data_response = $value;
                                    $data_response['answers'] = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id)->get();
                                    $data_response['response'] = SurveyPartnerInput::getALLSurveyPartnerInput($survey_id, $question_id, $partner_id)
                                        ->select('survey_partner_input_lines.suggested_answer_id')->get();
                                    $datas[$key] = $data_response;
                                    break;
                                case QuestionType::RATING_STAR:
                                    $data_response = $value;
                                    $data_response['answers'] = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id)->get();
                                    $data_response['response'] = SurveyPartnerInput::getALLSurveyPartnerInput($survey_id, $question_id, $partner_id)
                                        ->select('survey_partner_input_lines.value_star_rating', 'survey_partner_input_lines.suggested_answer_id')->get();
                                    $datas[$key] = $data_response;
                                    break;
                                case QuestionType::DATETIME_DATE:
                                    $data_response = $value;
                                    $datas[$key] = $data_response;
                                    $data_response['response'] = SurveyPartnerInput::getALLSurveyPartnerInput($survey_id, $question_id, $partner_id)
                                        ->select('survey_partner_input_lines.value_date')->get();
                                    break;
                                case QuestionType::DATETIME_DATE_RANGE:
                                    $data_response = $value;
                                    $datas[$key] = $data_response;
                                    $data_response['response'] = SurveyPartnerInput::getALLSurveyPartnerInput($survey_id, $question_id, $partner_id)
                                        ->select('survey_partner_input_lines.value_date_start', 'survey_partner_input_lines.value_date_end')->get();
                                    break;
                                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                                    $data_response = $value;
                                    $datas[$key] = $data_response;
                                    $data_response['response'] = SurveyPartnerInput::getALLSurveyPartnerInput($survey_id, $question_id, $partner_id)
                                        ->select('survey_partner_input_lines.value_text_box')->get();
                                    break;
                                default:
                                    return ClientResponse::responseError('question type không hợp lệ', $value['question_type']);
                                    break;
                            }
                        }
                        CommonCached::storeData($ckey, $datas, true);
                    }
                    return $datas;
                    return ClientResponse::responseSuccess('OK', $datas);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        }
    }
}
