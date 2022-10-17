<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Models\QuestionType;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use Illuminate\Http\Request;

class SurveyQuestionAnswersController extends Controller
{
    public function getListAnswers(Request $request)
    {
        try {
            $question_id = $request->question_id;
            $data = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id)
                ->orWhere('matrix_question_id', $question_id)->get();
            if (!$data) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function creatQuestionAnswers(Request $request)
    {
        try {
            $question_id = $request->question_id;
            $question_survey = SurveyQuestion::where('id', $question_id)->where('deleted',  SurveyQuestion::NOT_DELETED)->first();
            $input['sequence'] = $request->sequence;
            $input['value'] = $request->value;
            $input['question_id'] =  $question_id;
            if ($question_survey->question_type == QuestionType::MULTI_FACTOR_MATRIX) {
                $input['value_type'] = $request->value_type;
                if ($input['value_type'] == QuestionType::MATRIX_VALUE_COLUMN) {
                    $input['matrix_question_id'] = $question_id;
                    $input['question_id'] = 0;
                } else {
                    $input['question_id'] = $question_id;
                    $input['matrix_question_id'] = 0;
                }
            }
            $data = SurveyQuestionAnswer::create($input);
            if (!$data) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function updateQuestionAnswers(Request $request)
    {
        try {
            $answer_id = $request->answer_id;
            $question_answer = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($answer_id);
            if (!$question_answer) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            SurveyQuestionAnswer::updateSurveyQuestionAnswer(['deleted' => SurveyQuestionAnswer::DELETED], $answer_id);
            $question_id = $request->question_id;
            $question_survey = SurveyQuestion::where('id', $question_id)->where('deleted',  SurveyQuestion::NOT_DELETED)->first();
            $input = $request->all();
            $input['question_id'] =  $question_id;
            if ($question_survey->question_type == QuestionType::MULTI_FACTOR_MATRIX) {
                $input['value_type'] = $request->value_type;
                if ($input['value_type'] == QuestionType::MATRIX_VALUE_COLUMN) {
                    $input['matrix_question_id'] = $question_id;
                    $input['question_id'] = 0;
                } else {
                    $input['question_id'] = $question_id;
                    $input['matrix_question_id'] = 0;
                }
            }
            $result = SurveyQuestionAnswer::create($input);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Cập nhập thành công', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function creatQuestionAnswersDropdown(Request $request)
    {
        try {
            $question_id = $request->question_id;
            $question_answer = SurveyQuestionAnswer::select()->where('question_id', $question_id)->update(['deleted' => SurveyQuestionAnswer::DELETED]);
            $input['sequence'] = $request->sequence;
            $input['value'] = $request->value;
            $input['question_id'] =  $question_id;
            $arr = [];
            foreach ($request->option    as $key => $value) {
                $input['sequence'] = $key;
                $input['value'] = $value;
                $arr[] = $input;
            }
            $data = SurveyQuestionAnswer::insert($arr);
            if (!$data) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
