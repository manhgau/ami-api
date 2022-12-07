<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\CheckPackageUser;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CFunction;
use App\Helpers\Context;
use App\Helpers\FormatDate;
use App\Helpers\RemoveData;
use App\Models\AppSetting;
use App\Models\FormatDateType;
use App\Models\Package;
use App\Models\QuestionType;
use App\Models\QuestionTypeProfile;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use App\Models\SurveyTargets;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Models\TypeTarget;
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

    public function getDetailSurvey(Request $request)
    {
        try {
            $detail = Survey::getDetailSurvey($request->survey_id);
            if (!$detail) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $all_settings = AppSetting::getAllSetting();
            $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
            $detail->background ? $detail->background = $image_domain . $detail->background : null;
            $detail->real_end_time = date_format(date_create($detail->real_end_time), 'm-d-Y');
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function editSurvey(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'description' => 'string|max:255',
                'survey_id' => 'string|exists:App\Models\Survey,id',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $survey_user = Survey::getDetailSurvey($request->survey_id);
            if ($survey_user->state == Survey::STATUS_COMPLETED) {
                return ClientResponse::responseError('Không được sửa khảo sát này');
            }
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $data = $request->all();
            $request->real_end_time ? $data['real_end_time'] = FormatDate::formatDate($request->real_end_time) : null;
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
            $update_survey = Survey::updateSurvey($data, $request->survey_id);
            if (!$update_survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Update thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function deleteSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $survey_user = Survey::getDetailSurvey($request->survey_id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            Survey::destroy($survey_id);
            SurveyQuestion::deleteAllSurveyQuestions($survey_id);
            SurveyQuestionAnswer::deleteAllSurveyQuestionsAnswer($survey_id);
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
            if (CheckPackageUser::checkSurveykPackageUser($user_id)) {
                return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm lượt tạo khảo sát');
            }
            $input['user_id'] = $user_id;
            $input['title'] = $request->title ?? $survey_template->title . ' copy';
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
            $survey_questions = SurveyQuestion::getSurveyQuestion($ids);
            foreach ($survey_questions  as  $survey_question) {
                dd($survey_question);
            }
            return ClientResponse::responseSuccess('Thêm mới thành công', $survey);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
    // copy survey

    public static function copySurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            if (CheckPackageUser::checkSurveykPackageUser($user_id)) {
                return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm lượt tạo khảo sát');
            }
            $survey_detail = Survey::getDetailSurvey($survey_id);
            $survey_detail = json_decode(json_encode($survey_detail), true);
            $survey_detail['title'] = $survey_detail['title'] . '_copy';
            $survey_detail['id'] = CFunction::generateUuid();
            unset($survey_detail['created_at']);
            unset($survey_detail['updated_at']);
            unset($survey_detail['background']);
            $survey = Survey::create($survey_detail);
            if (!$survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $page_id = SurveyQuestion::NO_PAGE;
            $list_questions  = SurveyQuestion::getAllQuestionGroup($survey_id, $page_id)->toArray();
            foreach ($list_questions  as  $list_question) {
                $list_question = $list_question;
                $list_question['survey_id'] = $survey->id;
                $create_question = self::__copySurveyQuestion($list_question, $survey_id);
            }
            return ClientResponse::responseSuccess('Thêm mới thành công', $survey);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    private static function __copySurveyQuestion($survey_question, $survey_id)
    {
        if ($survey_question['question_type'] == QuestionType::GROUP) {
            $list_question_groups = SurveyQuestion::getAllQuestionGroup($survey_id, $survey_question['id'])->toArray();
            unset($survey_question['id']);
            unset($survey_question['created_at']);
            unset($survey_question['updated_at']);
            unset($survey_question['background']);
            $result = SurveyQuestion::createSurveyQuestion($survey_question);
            foreach ($list_question_groups as  $value) {
                $value['page_id'] =  $result->id;
                self::__copyQuestion($value, $value['id']);
            }
        } else {
            $result = self::__copyQuestion($survey_question, $survey_question['id']);
        }
        return true;
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
                    $value['survey_id'] = $survey_question['survey_id'];
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
        return true;
    }

    // Target Survey 

    public function getTargetSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $target = TypeTarget::getTypeTarget();
            $target_survey = SurveyTargets::getSurveyTarget($survey_id)->get()->groupBy('target_type');
            $data = [
                'target' => $target,
                'target_survey' => $target_survey ?? [],
            ];
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function createTargetSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $target_type = $request->target_type;
            $target_ids = $request->target_ids;
            $data = [];
            foreach ($target_ids as $target_id) {
                $input['target_id'] = $target_id;
                $input['survey_id'] = $survey_id;
                $input['target_type'] = $target_type;
                $data[] = $input;
            }
            $target_survey = SurveyTargets::getSurveyTarget($survey_id, $target_type)->delete();
            $create_target = SurveyTargets::insert($data);
            if (!$create_target) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $target_survey = SurveyTargets::getSurveyTarget($survey_id)->get()->groupBy('target_type');
            return ClientResponse::responseSuccess('OK', $target_survey);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
