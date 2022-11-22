<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\CheckPackageUser;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyQuestionController extends Controller
{
    public function createSurveyQuestion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'question_type' => 'required|string',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $input = $request->all();
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            if (CheckPackageUser::checkQuestionkPackageUser($user_id)) {
                return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng câu hỏi khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm câu hỏi khảo sát');
            }
            $input['created_by'] = $user_id;
            $input['survey_id'] = $request->survey_id;
            $input['count_questions'] =  SurveyQuestion::countQuestion($input['survey_id']) + 1;
            $question_type = Str::lower($request->question_type);
            if (QuestionType::checkQuestionTypeValid($question_type) === false) {
                return ClientResponse::responseError('Lỗi ! Không có dạng câu hỏi khảo sát này.');
            }
            $servey_question = SurveyQuestion::createSurveyQuestion($input);
            if (!$servey_question) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('OK', $servey_question);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getListSurveyQuestion(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $datas = SurveyQuestion::getListSurveyQuestion($survey_id);
            foreach ($datas as $key => $value) {
                $group_question = SurveyQuestion::listGroupQuestions($survey_id, $value->id);
                $value['group_question'] = $group_question;
                $datas[$key]  = $value;
            }
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }


    public function getDetailSurveyQuestion(Request $request)
    {
        try {
            $question_id = $request->question_id;
            if (empty($detail)) {
                $detail = SurveyQuestion::getDetailSurveyQuestion($question_id);
                if (!$detail) {
                    return ClientResponse::responseError('Không có bản ghi phù hợp');
                }
                $random =  $detail->validation_random;
                switch ($detail['question_type']) { // question_id 
                    case QuestionType::MULTI_FACTOR_MATRIX:
                        $detail['answers'] = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($detail['id'],  $random)->orWhere('matrix_question_id', $detail['id'])->get();
                        break;
                    case QuestionType::MULTI_CHOICE:
                    case QuestionType::MULTI_CHOICE_DROPDOWN:
                    case QuestionType::YES_NO:
                        $detail['answers'] = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($detail['id'],  $random)->get();
                        break;
                    case QuestionType::DATETIME_DATE:
                    case QuestionType::DATETIME_DATE_RANGE:
                    case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                    case QuestionType::QUESTION_ENDED_LONG_TEXT:
                    case QuestionType::NUMBER:
                    case QuestionType::RATING_STAR:
                    case QuestionType::RANKING:
                        break;
                    default:
                        return ClientResponse::responseError('question type không hợp lệ', $detail['question_type']);
                        break;
                }
            }
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public static function updateSurveyQuestion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $survey_user = SurveyQuestion::getDetailSurveyQuestion($request->question_id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $input = $request->all();
            $input['updated_by'] = $user_id;
            $update_survey = SurveyQuestion::updateSurveyQuestion($input, $request->question_id);
            if (!$update_survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Update thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public static function updateManySurveyQuestion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), []);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $input = $request->all();
            $result = self::__arrangeQuestion($input);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Update thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    private function __arrangeQuestion($input)
    {
        try {
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $data['updated_by'] = $user_id;
            foreach ($input as $key => $value) {
                $data['sequence'] = $value['sequence'];
                $result = SurveyQuestion::updateSurveyQuestion($data,  $value['question_id']);
                if (!$result) {
                    return false;
                }
            }
            return true;
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }



    public static function delSurveyQuestion(Request $request)
    {
        try {
            $survey_question = SurveyQuestion::getDetailSurveyQuestion($request->question_id);
            if (!$survey_question) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $del_survey = SurveyQuestion::updateSurveyQuestion(['deleted' => SurveyQuestion::DELETED], $request->question_id);
            if (!$del_survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $count_questions = SurveyQuestion::countQuestion($request->survey_id);
            Survey::updateSurvey(["question_count" => $count_questions], $request->survey_id);
            $list = SurveyQuestion::getAllQuestion($request->survey_id, $survey_question->page_id);
            $data = [];
            foreach ($list as $key => $value) {
                $input['question_id'] = $value->id;
                $input['sequence'] = $key + 1;
                $data[] = $input;
            }
            self::__arrangeQuestion($data);
            return ClientResponse::responseSuccess('Xóa thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public static function copySurveyQuestion(Request $request)
    {
        try {
            $question_id = $request->question_id;
            $survey_question = SurveyQuestion::getDetailSurveyQuestion($question_id);
            if (!$survey_question) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            switch ($survey_question['question_type']) { // question_id 
                case QuestionType::MULTI_FACTOR_MATRIX:
                case QuestionType::MULTI_CHOICE:
                case QuestionType::MULTI_CHOICE_DROPDOWN:
                case QuestionType::YES_NO:
                    $list_answer = SurveyQuestionAnswer::getAllAnswer($question_id);
                    unset($survey_question['id']);
                    unset($survey_question['created_at']);
                    unset($survey_question['updated_at']);
                    $survey_question['title'] = $survey_question['title'] . '_copy';
                    $survey_question = $survey_question->toArray();
                    $insert = SurveyQuestion::createSurveyQuestion($survey_question);
                    foreach ($list_answer as $key => $value) {

                        $value['question_id'] === 0 ? 0 : $value['question_id'] = $insert['id'];
                        $value['matrix_question_id'] === 0 ? 0 : $value['matrix_question_id'] = $insert['id'];
                        unset($value['id']);
                        $list_answer[$key] = $value;
                    }
                    SurveyQuestionAnswer::insert($list_answer);
                    break;
                case QuestionType::DATETIME_DATE:
                case QuestionType::DATETIME_DATE_RANGE:
                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                case QuestionType::NUMBER:
                case QuestionType::RATING_STAR:
                case QuestionType::RANKING:
                    unset($survey_question['id']);
                    unset($survey_question['created_at']);
                    unset($survey_question['updated_at']);
                    $survey_question['title'] = $survey_question['title'] . '_copy';
                    $survey_question = $survey_question->toArray();
                    $insert = SurveyQuestion::createSurveyQuestion($survey_question);
                    break;
                default:
                    return ClientResponse::responseError('question type không hợp lệ', $survey_question['question_type']);
                    break;
            }
            return ClientResponse::responseSuccess('Copy thành công', $insert);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
