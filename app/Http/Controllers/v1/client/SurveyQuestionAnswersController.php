<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Models\QuestionType;
use App\Models\Survey;
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
            $survey_id = $request->survey_id;
            $survey = Survey::getDetailSurvey($survey_id);
            $question_answer = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($answer_id);
            if (!$question_answer) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $input = $request->all();
            if ($survey->state == Survey::STATUS_ON_PROGRESS) {
                SurveyQuestionAnswer::updateSurveyQuestionAnswer(['deleted' => SurveyQuestionAnswer::DELETED], $answer_id);
                $input['question_id'] =  $question_answer->question_id;
                $input['matrix_question_id'] =  $question_answer->matrix_question_id;
                $input['value_type'] =  $question_answer->value_type;
                $input['sequence'] =  $question_answer->sequence;
                $result = SurveyQuestionAnswer::create($input);
                if (!$result) {
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
                return ClientResponse::responseSuccess('Cập nhập thành công', $result);
            }
            $update_anser = SurveyQuestionAnswer::updateSurveyQuestionAnswer($input, $answer_id);
            if (!$update_anser) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $question_answer_detail = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($answer_id);
            return ClientResponse::responseSuccess('Cập nhập thành công', $question_answer_detail);
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
