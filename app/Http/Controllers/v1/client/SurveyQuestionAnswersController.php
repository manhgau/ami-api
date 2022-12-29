<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Context;
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
            $input['survey_id'] = $request->survey_id;
            $input['sequence'] = $request->sequence;
            $input['value'] = ucfirst($request->value);
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
            $question_id = $request->question_id;
            $survey = Survey::getDetailSurvey($survey_id);
            $question_answer = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($answer_id);
            if (!$question_answer) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $input = $request->all();
            $request->value ? $input['value'] = ucfirst($request->value) : "";
            if ($survey->state == Survey::STATUS_ON_PROGRESS) {
                if ($request->deleted && $request->deleted  == 1) {
                    $result = SurveyQuestionAnswer::updateSurveyQuestionAnswer($input, $answer_id);
                    $list = SurveyQuestionAnswer::getAllAnswer($question_id);
                    foreach ($list as $key => $value) {
                        $update_anser = SurveyQuestionAnswer::updateSurveyQuestionAnswer(['sequence' => $key + 1], $value['id']);
                    }
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Xóa thành công', $result);
                }
                SurveyQuestionAnswer::updateSurveyQuestionAnswer(['deleted' => SurveyQuestionAnswer::DELETED], $answer_id);
                $input['survey_id'] =  $question_answer->survey_id;
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
            if ($request->deleted && $request->deleted  == 1) {
                SurveyQuestionAnswer::destroy($answer_id);
                $list = SurveyQuestionAnswer::getAllAnswer($question_id);
                foreach ($list as $key => $value) {
                    $update_anser = SurveyQuestionAnswer::updateSurveyQuestionAnswer(['sequence' => $key + 1], $value['id']);
                }
            } else {
                $update_anser = SurveyQuestionAnswer::updateSurveyQuestionAnswer($input, $answer_id);
                if (!$update_anser) {
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
            }
            $question_answer_detail = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($answer_id);
            return ClientResponse::responseSuccess('Cập nhập thành công', $question_answer_detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function updateLogicQuestionAnswers(Request $request)
    {
        try {
            $answer_id = $request->answer_id;
            $survey_id = $request->survey_id;
            $survey = Survey::getDetailSurvey($survey_id);
            $question_answer = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($answer_id);
            if (!$question_answer && !$survey) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $input = $request->all();
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
            $survey_id = $request->survey_id;
            $question_id = $request->question_id;
            $survey = Survey::getDetailSurvey($survey_id);
            if ($survey->state == Survey::STATUS_DRAFT) {
                SurveyQuestionAnswer::deleteAllSurveyQuestionsAnswer($survey_id, $question_id);
            }
            $question_answer_number = SurveyQuestionAnswer::select()->where('question_id', $question_id)->where(['deleted' => SurveyQuestionAnswer::NOT_DELETED])->count();
            $input['survey_id'] = $request->survey_id;
            $input['sequence'] = $request->sequence;
            $input['value'] = $request->value;
            $input['question_id'] =  $question_id;
            $arr = [];
            if (is_array($request->option) && count($request->option)) {
                foreach ($request->option    as $key => $value) {
                    $input['sequence'] = $question_answer_number + $key + 1;
                    $input['value'] = ucfirst($value);
                    $arr[] = $input;
                }
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
