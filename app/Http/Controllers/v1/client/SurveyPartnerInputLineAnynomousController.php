<?php

namespace App\Http\Controllers\v1\client;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\FormatDate;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;

class SurveyPartnerInputLineAnynomousController extends Controller
{

    public function surveyPartnerInputLineAnynomous(Request $request)
    {
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
            $input['question_id']   = $question_id;
            $input['survey_id']     = $survey_id;
            $input['partner_input_id']   = (int)$request->partner_input_id;
            $survey_question = SurveyQuestion::checkQuestionOfSurvey($survey_id, $question_id);
            if (!$survey_question) {
                return ClientResponse::responseError('Không tồn tại câu hỏi khảo sát');
            }
            $input['question_sequence']     = $survey_question->sequence;
            $input['answer_type']   = $survey_question->question_type;
            $input['answer_score']   = $request->answer_score ?? 0;
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
                    if (is_array($target_ids)) {
                        if ($survey_question->logic == SurveyQuestion::LOGIC && $survey_question->is_multiple == SurveyQuestion::NOT_MULTIPLE) {
                            $logic_come = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($target_ids[0])->logic_come;
                            $question_logic = SurveyQuestion::getQuestionByLogic($survey_id,  $logic_come);
                        }
                        foreach ($target_ids  as $key =>  $value) {
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

                    $input['suggested_answer_id'] = $request->suggested_answer_id;
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
                                'date'
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
                        $input['value_date'] = FormatDate::formatDate($request->value_date);
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
                                // 'max:' . $survey_question->validation_length_max,
                                // 'min:' . $survey_question->validation_length_min
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
                    $validator = Validator::make(
                        $request->all(),
                        [
                            'input' => [
                                $survey_question->validation_required ? 'required' : '',
                            ],
                        ],
                        [
                            'input.required' => 'Đây là một câu hỏi bắt buộc.', // custom message
                        ]
                    );
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return ClientResponse::response(ClientResponse::$validator_value, $errorString);
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
            $result = $question_logic ?? $result;
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            SurveyQuestion::updateSurveyQuestion(
                [
                    "view" => $survey_question->view + 1,
                ],
                $question_id
            );
            return ClientResponse::responseSuccess('Trả lời thành công', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
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
}
