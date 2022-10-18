<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\Context;
use App\Models\Package;
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
            if ((SurveyQuestion::countQuestion($user_id)) >= (Package::checkTheUserPackage($user_id)->limit_questions)) {
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
            $ckey  = CommonCached::cache_find_survey_question_by_question_id . "_" . $question_id;
            $detail = CommonCached::getData($ckey);
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
                    case QuestionType::MULTI_CHOICE_CHECKBOX:
                    case QuestionType::MULTI_CHOICE_RADIO:
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
                CommonCached::storeData($ckey, $detail, true);
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
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $data['updated_by'] = $user_id;
            foreach ($input as $key => $value) {
                $data['sequence'] = $value['sequence'];
                $result = SurveyQuestion::updateSurveyQuestion($data,  $value['question_id']);
                if (!$result) {
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
            }
            return ClientResponse::responseSuccess('Update thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public static function delSurveyQuestion(Request $request)
    {
        try {
            $survey_user = SurveyQuestion::getDetailSurveyQuestion($request->question_id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $del_survey = SurveyQuestion::updateSurveyQuestion(['deleted' => SurveyQuestion::DELETED], $request->question_id);
            if (!$del_survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $count_questions = SurveyQuestion::countQuestion($request->survey_id);
            Survey::updateSurvey(["question_count" => $count_questions], $request->survey_id);
            return ClientResponse::responseSuccess('Xóa thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
