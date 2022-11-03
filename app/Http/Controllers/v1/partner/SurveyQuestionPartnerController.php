<?php

namespace App\Http\Controllers\v1\partner;

use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\QuestionType;
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
                    $perPage = $request->per_page ?? 10;
                    $page = $request->current_page ?? 1;
                    $partner_id = $partner->id ?? 0;
                    if (!SurveyPartner::checkSurveyPartner($survey_id, $partner_id)) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    $lists = SurveyQuestion::getListQuestion($survey_id, $perPage, $page);
                    $lists = RemoveData::removeUnusedData($lists);
                    if (!$lists) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    $datas = [];
                    foreach ($lists['data'] as $key => $value) {
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
                                $data_response = $value;
                                $datas[$key] = $data_response;
                                break;
                            default:
                                return ClientResponse::responseError('question type không hợp lệ', $value['question_type']);
                                break;
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
}
