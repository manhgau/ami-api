<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\CheckPackageUser;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\AppSetting;
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
                'sequence' => 'required|integer',
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
            $survey_id = $request->survey_id;
            $input['title'] = ucfirst($request->title);
            $request->description ? $input['description'] = ucfirst($request->description) : "";
            $input['created_by'] = $user_id;
            $input['survey_id'] = $survey_id;
            $count_questions = SurveyQuestion::countSequence($survey_id, SurveyQuestion::NO_PAGE);
            $input_survey['question_count'] =   $count_questions + 1;
            Survey::updateSurvey($input_survey,  $survey_id);
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
            $question_id = $request->question_id ?? null;
            $datas = SurveyQuestion::getListSurveyQuestion($survey_id, $question_id);
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

    public function getListQuestionLogic(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $question_id = $request->question_id;
            $datas = SurveyQuestion::getListSurveyQuestion($survey_id, $question_id);
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $data = [];
            foreach ($datas as $value) {
                if ($value['is_page'] == SurveyQuestion::IS_PAGE) {
                    $group_question = SurveyQuestion::listGroupQuestions($survey_id, $value->id);
                    foreach ($group_question as $item) {
                        $item->sequence_group = $value->sequence;
                        array_push($data, $item);
                    }
                } else {
                    $value->sequence_group = 0;
                    array_push($data, $value);
                }
            }
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }


    public function getDetailSurveyQuestion(Request $request)
    {
        try {
            $question_id = $request->question_id;
            if (empty($detail)) {
                $detail = self::__getDetailSurveyQuestion($question_id);
            }
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    private function __getDetailSurveyQuestion($question_id)
    {
        try {
            $detail = SurveyQuestion::getDetailSurveyQuestion($question_id);
            if (!$detail) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $all_settings = AppSetting::getAllSetting();
            $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
            $detail->background ? $detail->background = $image_domain . $detail->background : null;
            $random =  $detail->validation_random;
            switch ($detail->question_type) { // question_id 
                case QuestionType::MULTI_FACTOR_MATRIX:
                    $detail->answers = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($detail->id,  $random)->orWhere('matrix_question_id', $detail->id)->get();
                    break;
                case QuestionType::MULTI_CHOICE:
                case QuestionType::MULTI_CHOICE_DROPDOWN:
                case QuestionType::YES_NO:
                    $detail->answers = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($detail->id,  $random)->get();
                    break;
                case QuestionType::DATETIME_DATE:
                case QuestionType::DATETIME_DATE_RANGE:
                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                case QuestionType::NUMBER:
                case QuestionType::RATING_STAR:
                case QuestionType::RANKING:
                case QuestionType::GROUP:
                    $detail->answers = [];
                    break;
                default:
                    return ClientResponse::responseError('question type không hợp lệ', $detail->question_type);
                    break;
            }
            return $detail;
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public static function updateSurveyQuestion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'description' => 'string|max:255',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $survey_id = $request->survey_id;
            $question_id = $request->question_id;
            $input = $request->all();
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $survey = Survey::getDetailSurvey($survey_id);
            $survey_user = SurveyQuestion::getDetailSurveyQuestion($question_id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            if ($request->question_type && $survey->state == Survey::STATUS_DRAFT) {
                if (($request->question_type  == QuestionType::MULTI_CHOICE && $survey_user->question_type ==  QuestionType::MULTI_CHOICE_DROPDOWN)
                    || ($request->question_type  == QuestionType::MULTI_CHOICE_DROPDOWN && $survey_user->question_type ==  QuestionType::MULTI_CHOICE)
                ) {
                    $input['updated_by'] = $user_id;
                    $update_question = SurveyQuestion::updateSurveyQuestion($input,  $question_id);
                    if (!$update_question) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    $detail = self::__getDetailSurveyQuestion($question_id);
                    return ClientResponse::responseSuccess('Update thành công', $detail);
                } else {
                    SurveyQuestionAnswer::deleteAllSurveyQuestionsAnswer($survey_id, $question_id);
                    switch ($request->question_type) { // question_id 
                        case QuestionType::MULTI_FACTOR_MATRIX:
                            $input['is_page'] = SurveyQuestion::NO_PAGE;
                            $data_insert = [
                                [
                                    "survey_id" => $survey_id,
                                    "question_id" => $question_id,
                                    "sequence" => 1,
                                    "value_type" => QuestionType::MATRIX_VALUE_ROW,
                                    "value" => "Hàng 1"
                                ],
                                [
                                    "survey_id" => $survey_id,
                                    "question_id" => $question_id,
                                    "sequence" => 2,
                                    "value_type" => QuestionType::MATRIX_VALUE_ROW,
                                    "value" => "Hàng 2"
                                ],
                                [
                                    "survey_id" => $survey_id,
                                    "matrix_question_id" => $question_id,
                                    "sequence" => 1,
                                    "value_type" => QuestionType::MATRIX_VALUE_COLUMN,
                                    "value" => "Cột 1"
                                ],
                                [
                                    "survey_id" => $survey_id,
                                    "matrix_question_id" => $question_id,
                                    "sequence" => 2,
                                    "value_type" => QuestionType::MATRIX_VALUE_COLUMN,
                                    "value" => "Cột 2"
                                ]

                            ];
                            SurveyQuestionAnswer::insert($data_insert);
                            break;
                        case QuestionType::MULTI_CHOICE:
                        case QuestionType::MULTI_CHOICE_DROPDOWN:
                            $input['is_page'] = SurveyQuestion::NO_PAGE;
                            $data_insert = [
                                [
                                    "survey_id" => $survey_id,
                                    "question_id" => $question_id,
                                    "sequence" => 1,
                                    "value" => "Lựa chọn 1"
                                ],
                                [
                                    "survey_id" => $survey_id,
                                    "question_id" => $question_id,
                                    "sequence" => 2,
                                    "value" => "Lựa chọn 2"
                                ]

                            ];
                            SurveyQuestionAnswer::insert($data_insert);
                            break;
                        case QuestionType::YES_NO:
                            $input['is_page'] = SurveyQuestion::NO_PAGE;
                            $data_insert = [
                                [
                                    "survey_id" => $survey_id,
                                    "question_id" => $question_id,
                                    "sequence" => 1,
                                    "value" => "Có"
                                ],
                                [
                                    "survey_id" => $survey_id,
                                    "question_id" => $question_id,
                                    "sequence" => 2,
                                    "value" => "Không"
                                ]

                            ];
                            SurveyQuestionAnswer::insert($data_insert);
                            break;
                        case QuestionType::DATETIME_DATE:
                        case QuestionType::DATETIME_DATE_RANGE:
                        case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                        case QuestionType::QUESTION_ENDED_LONG_TEXT:
                        case QuestionType::NUMBER:
                        case QuestionType::RATING_STAR:
                        case QuestionType::RANKING:
                            $input['is_page'] = SurveyQuestion::NO_PAGE;
                            break;
                        case QuestionType::GROUP:
                            $input['is_page'] = SurveyQuestion::IS_PAGE;

                            break;
                        default:
                            return ClientResponse::responseError('question type không hợp lệ', $request->question_type);
                            break;
                    }
                    $input['updated_by'] = $user_id;
                    $update_question = SurveyQuestion::updateSurveyQuestion($input,  $question_id);
                    if (!$update_question) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    $detail = self::__getDetailSurveyQuestion($question_id);
                    return ClientResponse::responseSuccess('Update thành công', $detail);
                }
            }
            $request->description ? $input['description'] = ucfirst($request->description) : "";
            $request->title ? $input['title'] = ucfirst($request->title . ' ') : "";
            $input['updated_by'] = $user_id;
            $update_survey = SurveyQuestion::updateSurveyQuestion($input, $question_id);
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
            $survey_id = $request->survey_id;
            $question_id = $request->question_id;
            $survey_question = SurveyQuestion::getDetailSurveyQuestion($question_id);
            if (!$survey_question) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            SurveyQuestion::destroy($question_id);
            if ($survey_question->question_type == QuestionType::GROUP) {
                $list_questions = SurveyQuestion::getAllQuestion($survey_id, $survey_question->id);
                foreach ($list_questions as $value) {
                    SurveyQuestion::destroy($value->id);
                    SurveyQuestionAnswer::deleteAllSurveyQuestionsAnswer($survey_id, $value->id);
                }
            }
            SurveyQuestionAnswer::deleteAllSurveyQuestionsAnswer($survey_id, $question_id);
            $count_questions = SurveyQuestion::countSequence($survey_id, SurveyQuestion::NO_PAGE);
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
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            if (CheckPackageUser::checkQuestionkPackageUser($user_id)) {
                return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng câu hỏi khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm câu hỏi khảo sát');
            }
            $survey_question = SurveyQuestion::getDetailSurveyQuestion($question_id);
            if (!$survey_question) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $survey_question = json_decode(json_encode($survey_question), true);
            $survey_question['title'] = $survey_question['title'] . '_copy';
            $survey_question['sequence'] = $survey_question['sequence'] . '.' . 1;
            if ($survey_question['question_type'] == QuestionType::GROUP) {
                $list_question_groups = SurveyQuestion::getAllQuestionGroup($survey_question['survey_id'], $survey_question['id']);
                unset($survey_question['id']);
                unset($survey_question['created_at']);
                unset($survey_question['updated_at']);
                unset($survey_question['background']);
                $result = SurveyQuestion::createSurveyQuestion($survey_question);
                if (!$result) {
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
                foreach ($list_question_groups as $key => $value) {
                    $value = json_decode(json_encode($value), true);
                    $value['page_id'] =  $result->id;
                    self::__copyQuestion($value, $value['id']);
                }
            } else {
                $result = self::__copyQuestion($survey_question, $question_id);
            }
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $list = SurveyQuestion::getAllQuestion($request->survey_id, $survey_question['page_id']);
            $data = [];
            foreach ($list as $key => $value) {
                $input['question_id'] = $value->id;
                $input['sequence'] = $key + 1;
                $data[] = $input;
            }
            self::__arrangeQuestion($data);
            return ClientResponse::responseSuccess('Copy thành công', self::__getDetailSurveyQuestion($result['id']));
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    private static function __copyQuestion($survey_question, $question_id)
    {
        switch ($survey_question['question_type']) { // question_id 
            case QuestionType::MULTI_FACTOR_MATRIX:
            case QuestionType::MULTI_CHOICE:
            case QuestionType::MULTI_CHOICE_DROPDOWN:
            case QuestionType::YES_NO:
                $list_answer = SurveyQuestionAnswer::getAllAnswer($question_id);
                unset($survey_question['id']);
                unset($survey_question['created_at']);
                unset($survey_question['updated_at']);
                unset($survey_question['background']);
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
                unset($survey_question['background']);
                $insert = SurveyQuestion::createSurveyQuestion($survey_question);
                break;
            default:
                return ClientResponse::responseError('question type không hợp lệ', $survey_question['question_type']);
                break;
        }
        return $insert;
    }
}
