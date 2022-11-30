<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\CheckPackageUser;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CFunction;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\FormatDateType;
use App\Models\Package;
use App\Models\QuestionType;
use App\Models\QuestionTypeProfile;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Models\SurveyUser;
use Carbon\Carbon;
use Validator;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function createSurvey(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $input = $request->all();
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            if (CheckPackageUser::checkSurveykPackageUser($user_id)) {
                return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm lượt tạo khảo sát');
            }
            $input['user_id'] = $user_id;
            $input['id'] = CFunction::generateUuid();
            $input['created_by'] = $user_id;
            $input['start_time'] = Carbon::now();
            $survey = Survey::create($input);
            if (!$survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Thêm mới thành công', $survey);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getListSurvey(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $state = $request->state;
            $is_anynomous = $request->is_anynomous;
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $datas = Survey::getListSurvey($perPage,  $page, $user_id, $state);
            $arr = [];
            foreach ($datas['data'] as $key => $value) {
                $count_response = SurveyPartnerInput::countSurveyInput($value['id'], $is_anynomous);
                $arr = $value;
                $arr['count_response'] = $count_response;
                $datas['data'][$key] = $arr;
            }
            $datas = RemoveData::removeUnusedData($datas);
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getDetailSurvey($id)
    {
        try {
            $detail = Survey::getDetailSurvey($id);
            if (!$detail) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function editSurvey(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'category_id' => 'integer|max:20',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $survey_user = Survey::getDetailSurvey($id);
            if ($survey_user->state == Survey::STATUS_COMPLETED) {
                return ClientResponse::responseError('Không được sửa khảo sát này');
            }
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $data = $request->all();
            $request->real_end_time ?? $data['end_time'] = $request->real_end_time;
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $data['user_id'] = $user_id;
            $data['updated_by'] = $user_id;
            $texts = '';
            if ($request->note) {
                foreach ($data['note'] as $key => $value) {
                    $typeProfile = QuestionTypeProfile::getTypeProfile();
                    $string = '<span><strong>' . $typeProfile[$value['key']] . ': </strong>' . json_encode($value['value'], JSON_UNESCAPED_UNICODE) . '</span><br>';
                    $texts = $texts . $string;
                }
                $data['note'] = $texts;
            }
            $update_survey = Survey::updateSurvey($data, $id);
            if (!$update_survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Update thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function deleteSurvey($id)
    {
        try {
            $survey_user = Survey::getDetailSurvey($id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $del_survey = Survey::updateSurvey(['deleted' => Survey::DELETED], $id);
            if (!$del_survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Xóa thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getQuestionType()
    {
        try {
            $result = QuestionType::getTypeQuestionBygroup();
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('OK', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getFormatDateType()
    {
        try {
            $result = FormatDateType::getFormatDateType();
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('OK', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public static function useSurveyTemplate(Request $request)
    {
        try {
            $survey_template_id = $request->survey_template_id;
            $survey_template = SurveyTemplate::getDetailSurveyTemplate($survey_template_id);
            if (!$survey_template) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);

            if ((Survey::countSurvey($user_id)) >= (Package::checkTheUserPackage($user_id)->limit_projects) || (Package::checkTheUserPackage($user_id)->limit_projects) === 0) {
                return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm lượt tạo khảo sát');
            }
            $input['user_id'] = $user_id;
            $input['title'] = $request->title ?? $survey_template->title . ' copy';
            $input['category_id'] = $survey_template->category_id;
            $input['active'] = Survey::ACTIVE;
            $input['id'] = CFunction::generateUuid();
            $input['created_by'] = $user_id;
            $survey = Survey::create($input);
            if (!$survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $survey_template_question = SurveyTemplateQuestion::getSurveyTemplateQuestion($survey_template_id);
            $ids = [];
            foreach ($survey_template_question   as $key => $value) {
                $ids[$key] = $value['survey_question_id'];
            }
            $question = SurveyQuestion::getSurveyQuestion($ids);
            foreach ($question  as $key => $value) {
                $question_type = $value['question_type'];
                $input_question = [];
                $input_question = $value;
                $input_question = json_encode($input_question);
                $input_question = json_decode($input_question, true);
                $input_question['survey_id'] = $survey->id;
                $input_question['created_by'] = $user_id;
                switch ($question_type) {
                    case QuestionType::MULTI_CHOICE:
                    case QuestionType::MULTI_CHOICE_DROPDOWN:
                    case QuestionType::RATING_STAR:
                    case QuestionType::YES_NO:
                        $servey_question = SurveyQuestion::createSurveyQuestion($input_question);
                        if (!$servey_question) {
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $data = [];
                        $survey_question_answer = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($value['id'])->get();
                        foreach ($survey_question_answer as $key => $item) {
                            unset($item['id']);
                            $data_insert = $item;
                            $data_insert['question_id'] = $servey_question->id;
                            $data[$key] = json_decode(json_encode($data_insert), true);
                        }
                        $result = SurveyQuestionAnswer::insert($data);
                        if (!$result) {
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $result_data[$key] =  $servey_question;
                        break;
                    case QuestionType::DATETIME_DATE:
                    case QuestionType::DATETIME_DATE_RANGE:
                    case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                    case QuestionType::QUESTION_ENDED_LONG_TEXT:
                        $servey_question = SurveyQuestion::createSurveyQuestion($input_question);
                        if (!$servey_question) {
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $result_data[$key] =  $servey_question;
                        break;
                    case QuestionType::MULTI_FACTOR_MATRIX:
                        $servey_question = SurveyQuestion::createSurveyQuestion($input_question);
                        if (!$servey_question) {
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $data = [];
                        $survey_question_answer = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($value['id'])->orWhere('matrix_question_id', $value['id'])->get();
                        foreach ($survey_question_answer as $key => $item) {
                            unset($item['id']);
                            $data_insert = $item;
                            if ($item['matrix_question_id']) {
                                $data_insert['matrix_question_id'] = $servey_question->id;
                                $data_insert['question_id'] = 0;
                            } else {
                                $data_insert['question_id'] = $servey_question->id;
                                $data_insert['matrix_question_id'] = 0;
                            }
                            $data[$key] = json_decode(json_encode($data_insert), true);
                        }
                        $result = SurveyQuestionAnswer::insert($data);
                        if (!$result) {
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $result_data[$key] =  $servey_question;
                        break;
                    default:
                        return ClientResponse::responseError('question type không hợp lệ', $question_type);
                        break;
                }
            }
            return ClientResponse::responseSuccess('Thêm mới thành công', $survey);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
