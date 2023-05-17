<?php

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\QuestionType;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;

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
                        return ClientResponse::response(ClientResponse::$validator_value, $errorString);
                    }
                    $question_id = $request->question_id ?? 0;
                    $survey_id = $request->survey_id ?? 0;
                    $partner_input_id = $request->partner_input_id ?? 0;
                    SurveyPartnerInputLine::deletePartnerInputLine($survey_id, $partner_input_id, $question_id);
                    $input['question_id']   = $question_id;
                    $input['survey_id']     = $survey_id;
                    $input['partner_input_id']   = $request->partner_input_id;
                    $survey_question = SurveyQuestion::checkQuestionOfSurvey($survey_id, $question_id);
                    if (!$survey_question) {
                        return ClientResponse::responseError('Dự án không có câu hỏi này');
                    }
                    $input['question_sequence']     = $survey_question->sequence;
                    $input['answer_type']   = $survey_question->question_type;
                    $input['created_by']   = $partner->id ?? 0;
                    $input['answer_score']   = $request->answer_score ?? 0;
                    // to do ....   
                    $data_input = [];
                    switch ($input['answer_type']) {
                        case QuestionType::MULTI_CHOICE:
                        case QuestionType::MULTI_CHOICE_DROPDOWN:
                        case QuestionType::YES_NO:
                            $validator = Validator::make(
                                $request->all(),
                                [
                                    'suggested_answer_id' => [
                                        $survey_question->validation_required ? 'required' : '',
                                    ],
                                ],
                                [
                                    'suggested_answer_id.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                                ]
                            );
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::response(ClientResponse::$validator_value, $errorString);
                            }
                            $target_ids = $request->suggested_answer_id;
                            // if (is_array($target_ids)) {
                            //     foreach ($target_ids  as $key => $value) {
                            //         $input['suggested_answer_id'] = $value;
                            //         $data_input[$key] = $input;
                            // }
                            if (is_array($target_ids)) {
                                foreach ($target_ids  as $key => $value) {
                                    $input['suggested_answer_id'] = $value;
                                    $data_input[$key] = $input;
                                }
                            }
                            break;
                        case QuestionType::RATING_STAR:
                            $validator = Validator::make(
                                $request->all(),
                                [
                                    'value_rating_ranking' => [
                                        $survey_question->validation_required ? 'required' : '',
                                    ],
                                ],
                                [
                                    'value_rating_ranking.required' => 'Đây là một câu hỏi bắt buộc.', // custom message    
                                ]
                            );
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::response(ClientResponse::$validator_value, $errorString);
                            }

                            $input['value_rating_ranking'] = $request->value_rating_ranking;
                            $data_input = $input;
                            break;
                        case QuestionType::RANKING:
                            $validator = Validator::make(
                                $request->all(),
                                [
                                    'value_rating_ranking' => [
                                        $survey_question->validation_required ? 'required' : '',
                                    ],
                                ],
                                [
                                    'value_rating_ranking.required' => 'Đây là một câu hỏi bắt buộc.', // custom message 
                                ]
                            );
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::response(ClientResponse::$validator_value, $errorString);
                            }
                            $input['value_rating_ranking'] = $request->value_rating_ranking;
                            $input['value_rating_ranking'] ? $input['value_level_ranking'] = SurveyQuestion::getNameLevelRanking($question_id)[$request->value_rating_ranking] : '';
                            $data_input = $input;
                            break;
                        case QuestionType::DATETIME_DATE:
                            $validator = Validator::make(
                                $request->all(),
                                [
                                    'value_date' => [
                                        $survey_question->validation_required ? 'required' : '',
                                    ],
                                ],
                                [
                                    'value_date.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                                ]
                            );
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::response(ClientResponse::$validator_value, $errorString);
                            }
                            if ($request->all()) {
                                $input['value_date'] = $request->value_date;
                                $data_input = $input;
                            }
                            break;
                        case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                            $validator = Validator::make(
                                $request->all(),
                                [
                                    'value_text_box' => [
                                        $survey_question->validation_required ? 'required' : '',
                                        'string',
                                        'max:255'
                                    ],
                                ],
                                [
                                    'value_text_box.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                                    'value_text_box.string' => 'Câu hỏi nhận dữ liệu kiểu chuỗi.', // custom message     
                                    'value_text_box.max' => 'Số ký tự không vượt quá 255 ký tự.', // custom message       
                                ]
                            );
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::response(ClientResponse::$validator_value, $errorString);
                            }
                            $input['value_text_box'] = $request->value_text_box ?? '';
                            $data_input = $input;
                            break;
                        case QuestionType::QUESTION_ENDED_LONG_TEXT:
                            $validator = Validator::make(
                                $request->all(),
                                [
                                    'value_char_box' => [
                                        $survey_question->validation_required ? 'required' : '',
                                        'string',
                                        'max:1000'
                                    ],
                                ],
                                [
                                    'value_char_box.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                                    'value_char_box.string' => 'Câu hỏi nhận dữ liệu kiểu chuỗi.', // custom message   
                                    'value_char_box.max' => 'Số ký tự không vượt quá 1000 ký tự.', // custom message      
                                ]
                            );
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::response(ClientResponse::$validator_value, $errorString);
                            }
                            $input['value_char_box'] = $request->value_char_box ?? '';
                            $data_input = $input;
                            break;
                        case QuestionType::NUMBER:
                            $validator = Validator::make(
                                $request->all(),
                                [
                                    'value_number' => [
                                        'integer',
                                        $survey_question->validation_required ? 'required' : '',
                                    ],
                                ],
                                [
                                    'value_number.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                                    'value_number.integer' => 'Câu hỏi nhận dữ liệu kiểu số.', // custom message     
                                ]
                            );
                            if ($validator->fails()) {
                                $errorString = implode(",", $validator->messages()->all());
                                return ClientResponse::response(ClientResponse::$validator_value, $errorString);
                            }
                            $input['value_number'] = (int)$request->value_number ?? '';
                            $data_input = $input;
                            break;
                        case QuestionType::MULTI_FACTOR_MATRIX:
                            if ($survey_question->validation_required) {
                                if (empty($request->all())) {
                                    return ClientResponse::response(ClientResponse::$validator_value, 'Đây là một câu hỏi bắt buộc.');
                                }
                            }
                            $data = $request->all();
                            if (is_array($data) &&  count($data) > 0) {
                                foreach ($data  as $key => $value) {
                                    if (is_array($value['matrix_column_id']) &&  count($value['matrix_column_id']) > 0) {
                                        foreach ($value['matrix_column_id'] as $item) {
                                            $input['matrix_column_id'] = $item;
                                            $input['matrix_row_id'] = $value['matrix_row_id'];
                                            $data_input[] = $input;
                                        }
                                    }
                                }
                            }
                            break;
                        default:
                            return ClientResponse::responseError('question type không hợp lệ', $input['answer_type']);
                            break;
                    }
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
                        return ClientResponse::responseSuccess('Bỏ qua thành công', true);
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
                    $result = $question_logic ?? null;
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
