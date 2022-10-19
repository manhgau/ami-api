<?php

namespace App\Http\Controllers\v1\client;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\QuestionType;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;

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
                return ClientResponse::responseError($errorString);
            }
            $question_id = $request->question_id ?? 0;
            $input['question_id']   = $question_id;
            $input['survey_id']     = $request->survey_id;
            $input['partner_input_id']   = (int)$request->partner_input_id;
            $survey_question = SurveyQuestion::getDetailSurveyQuestion($question_id);
            $input['question_sequence']     = $survey_question->sequence;
            $input['answer_type']   = $survey_question->question_type;
            $input['answer_score']   = $request->answer_score ?? 0;
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
                        foreach ($target_ids  as $key =>  $value) {
                            $input['suggested_answer_id'] = $value;
                            $data_input[$key] = $input;
                        }
                    }
                    break;
                case QuestionType::RATING_STAR:
                    $validator = Validator::make($request->all(), [
                        'suggested_answer_id' => [
                            $survey_question->validation_required ? 'required' : '',
                        ],
                        'value_star_rating' => [
                            $survey_question->validation_required ? 'required' : '',
                        ],
                    ]);
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return ClientResponse::responseError($errorString);
                    }

                    $input['suggested_answer_id'] = $request->suggested_answer_id;
                    $input['value_star_rating'] = $request->value_star_rating;
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
                    $input['value_date'] = $request->value_date ?? '';
                    $data_input = $input;
                    break;
                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                    $validator = Validator::make($request->all(), [
                        'value_text_box' => [
                            $survey_question->validation_required ? 'required' : '',
                            'string',
                            $survey_question->validation_required ? 'max:' . $survey_question->validation_length_max : '',
                            $survey_question->validation_required ? 'min:' . $survey_question->validation_length_min : ''
                        ],
                    ]);
                    if ($validator->fails()) {
                        $errorString = implode(",", $validator->messages()->all());
                        return ClientResponse::responseError($errorString);
                    }
                    $input['value_text_box'] = $request->value_text_box ?? '';
                    break;
                case QuestionType::NUMBER:
                    $validator = Validator::make($request->all(), [
                        'value_number' => [
                            'integer',
                            $survey_question->validation_required ? 'required' : '',
                            'max:' . $survey_question->validation_length_max,
                            'min:' . $survey_question->validation_length_min
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
                    $data = $request->all();
                    if (is_array($data)) {
                        foreach ($data  as  $key => $value) {
                            $input['matrix_row_id'] = $value['matrix_row_id'];
                            $input['matrix_column_id'] = $value['matrix_column_id'];
                            $data_input[$key] = $input;
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
                ],
                $question_id
            );
            return ClientResponse::responseSuccess('Trả lời thành công', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
