<?php

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Helpers\FormatDate;
use App\Helpers\LogicQuestionAnswer;
use App\Models\QuestionType;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;

class SurveyPartnerInputLineController extends Controller
{

    public function surveyPartnerInputLine(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $validator = Validator::make($request->all(), [
                        'survey_id' => 'string|exists:App\Models\Survey,id',
                        'partner_input_id' => 'integer|exists:App\Models\SurveyPartnerInput,id',
                        'question_id' => 'integer|exists:App\Models\SurveyQuestion,id',
                    ]);
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return ClientResponse::responseError($errorString);
                    }
                    $question_id = $request->question_id ?? 0;
                    $survey_id = $request->survey_id ?? 0;
                    $input['question_id']   = $question_id;
                    $input['survey_id']     = $survey_id;
                    $input['partner_input_id']   = $request->partner_input_id;
                    $survey_question = SurveyQuestion::checkQuestionOfSurvey($survey_id, $question_id);
                    if (!$survey_question) {
                        return ClientResponse::responseError('Khảo sát không có câu hỏi này');
                    }
                    $input['question_sequence']     = $survey_question->sequence;
                    $input['answer_type']   = $survey_question->question_type;
                    $input['created_by']   = $partner->id ?? 0;
                    $input['answer_score']   = $request->answer_score ?? 0;
                    if (!$request->all()) {
                        $input['skipped']   = SurveyPartnerInputLine::SKIP;
                        $result = SurveyPartnerInputLine::create($input);
                        if (!$result) {
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        SurveyQuestion::updateSurveyQuestion(
                            [
                                "skip_count" => $survey_question->skip_count + 1,
                                "view" => $survey_question->view + 1,
                            ],
                            $question_id
                        );
                        return ClientResponse::responseSuccess('Trả lời thành công', $result);
                    }
                    // to do ....   
                    $data_input = [];
                    switch ($input['answer_type']) {
                        case QuestionType::MULTI_CHOICE:
                        case QuestionType::MULTI_CHOICE_DROPDOWN:
                        case QuestionType::YES_NO:
                            $validator = Validator::make($request->all(), [
                                'suggested_answer_id' => [
                                    $survey_question->validation_required ? 'required' : '',
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }
                            $target_ids = $request->suggested_answer_id;

                            if (is_array($target_ids)) {
                                if ($survey_question->logic == SurveyQuestion::LOGIC && $survey_question->is_multiple == SurveyQuestion::NOT_MULTIPLE) {
                                    $logic_come = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($target_ids[0])->logic_come;
                                    $question_logic = SurveyQuestion::getQuestionByLogic($survey_id,  $logic_come);
                                }
                                foreach ($target_ids  as $key => $value) {
                                    $input['suggested_answer_id'] = $value;
                                    $data_input[$key] = $input;
                                }
                            }
                            break;
                        case QuestionType::RATING_STAR:
                            $validator = Validator::make($request->all(), [
                                'value_rating_ranking' => [
                                    $survey_question->validation_required ? 'required' : '',
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }

                            $input['value_rating_ranking'] = $request->value_rating_ranking;
                            $data_input = $input;
                            break;
                        case QuestionType::RANKING:
                            $validator = Validator::make($request->all(), [
                                'value_rating_ranking' => [
                                    $survey_question->validation_required ? 'required' : '',
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }

                            $input['value_rating_ranking'] = $request->value_rating_ranking;
                            $input['value_level_ranking'] = SurveyQuestion::getNameLevelRanking($question_id)[$request->value_rating_ranking];
                            $data_input = $input;
                            break;
                        case QuestionType::DATETIME_DATE_RANGE:
                            $validator = Validator::make($request->all(), [
                                'value_date_start' => [
                                    $survey_question->validation_required ? 'required' : '',
                                    'date'
                                ],
                                'value_date_end' => [
                                    $survey_question->validation_required ? 'required' : '',
                                    'date'
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }
                            $input['value_date_start'] = $request->value_date_start ?? '';
                            $input['value_date_end'] = $request->value_date_end ?? '';
                            $data_input = $input;
                            break;
                        case QuestionType::DATETIME_DATE:
                            $validator = Validator::make($request->all(), [
                                'value_date' => [
                                    $survey_question->validation_required ? 'required' : '',
                                    'date'
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }
                            $input['value_date'] = FormatDate::formatDate($request->value_date) ?? '';
                            $data_input = $input;
                            break;
                        case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                            $validator = Validator::make($request->all(), [
                                'value_text_box' => [
                                    $survey_question->validation_required ? 'required' : '',
                                    'string',
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }
                            $input['value_text_box'] = $request->value_text_box ?? '';
                            $data_input = $input;
                            break;
                        case QuestionType::QUESTION_ENDED_LONG_TEXT:
                            $validator = Validator::make($request->all(), [
                                'value_char_box' => [
                                    $survey_question->validation_required ? 'required' : '',
                                    'string',
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }
                            $input['value_char_box'] = $request->value_char_box ?? '';
                            $data_input = $input;
                            break;
                        case QuestionType::NUMBER:
                            $validator = Validator::make($request->all(), [
                                'value_number' => [
                                    'integer',
                                    $survey_question->validation_required ? 'required' : '',
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }
                            $input['value_number'] = (int)$request->value_number ?? '';
                            $data_input = $input;
                            break;
                        case QuestionType::MULTI_FACTOR_MATRIX:
                            $validator = Validator::make($request->all(), [
                                'input' => [
                                    $survey_question->validation_required ? 'required' : '',
                                ],
                            ]);
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::responseError($errorString);
                            }
                            $data = $request->all();
                            if (is_array($data)) {
                                foreach ($data  as $key => $value) {
                                    foreach ($value['matrix_row_id'] as $item) {
                                        $input['matrix_row_id'] = $item;
                                        $input['matrix_column_id'] = $value['matrix_column_id'];
                                        $data_input[] = $input;
                                    }
                                }
                            }
                            break;
                        default:
                            return ClientResponse::responseError('question type không hợp lệ', $input['answer_type']);
                            break;
                    }
                    $result = SurveyPartnerInputLine::insert($data_input);
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    SurveyQuestion::updateSurveyQuestion(
                        [
                            "view" => $survey_question->view + 1,
                            'number_of_response' => $survey_question->number_of_response + 1
                        ],
                        $question_id
                    );
                    $question_logic ? $result = $question_logic : $result = $result;
                    return ClientResponse::responseSuccess('Trả lời thành công', $result);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
