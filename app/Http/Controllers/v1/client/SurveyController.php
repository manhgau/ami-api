<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\CheckPackageUser;
use App\Helpers\CheckResponseOfSurvey;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CFunction;
use App\Helpers\Context;
use App\Helpers\FormatDate;
use App\Helpers\FtpSv;
use App\Helpers\RemoveData;
use App\Models\AppSetting;
use App\Models\FormatDateType;
use App\Models\Images;
use App\Models\NotificationsFirebase;
use App\Models\NotificationsFirebaseClients;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use App\Models\SurveyTargets;
use App\Models\SurveyTemplate;
use App\Models\TypeTarget;
use App\Models\UserPackage;
use Carbon\Carbon;
use Validator;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function createSurvey(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'title' => 'required|string|max:255',
                ],
                [
                    'title.required' => 'Bạn chưa điền tiêu đề khảo sát.',
                ]
            );
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
            $input['title'] = ucfirst($request->title);
            $request->description ? $input['description'] = ucfirst($request->description) : "";
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
            $states = $request->states;
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $datas = Survey::getListSurvey($perPage,  $page, $user_id, $states);
            $array = [];
            foreach ($datas['data'] as $key => $value) {
                $data_url['id'] = $value['id'];
                $data_url['title'] = $value['title'];
                $data_url['link_url'] = $value['link_url'];
                $data_url['question_count'] = $value['question_count'];
                $data_url['state'] = $value['state'];
                $data_url['status_not_completed'] = $value['status_not_completed'];
                $value['start_time'] ? $data_url['start_time'] = date_format(date_create($value['start_time']), 'd/m/Y') : null;
                $value['real_end_time'] ? $data_url['real_end_time'] = date_format(date_create($value['real_end_time']), 'd/m/Y') : null;
                $value['created_at'] ? $data_url['created_at'] = date_format(date_create($value['created_at']), 'd/m/Y') : null;
                $value['updated_at'] ? $data_url['updated_at'] = date_format(date_create($value['updated_at']), 'd/m/Y') : null;
                $data_url['number_of_response'] = SurveyPartnerInput::countSurveyInput($value['id'], SurveyPartnerInput::ANYNOMOUS_TRUE);
                $data_url['limmit_of_response'] = $value['limmit_of_response_anomyous'];
                $data_url['data_from'] = Survey::URL;
                array_push($array, $data_url);
                if ($value['is_ami'] == Survey::DATA_URL_AND_AMI) {
                    $data_ami = $data_url;
                    $value['end_time'] ? $data_ami['real_end_time'] = date_format(date_create($value['end_time']), 'd/m/Y') : null;
                    $data_ami['limmit_of_response'] = $value['limmit_of_response'];
                    $data_ami['state'] = $value['state_ami'];
                    $data_ami['data_from'] = Survey::AMI;
                    $value['state_ami'] == Survey::STATUS_NOT_COMPLETED ? $data_ami['status_not_completed'] = Survey::TIME_UP : null;
                    $data_ami['number_of_response'] = SurveyPartnerInput::countSurveyInput($value['id'], SurveyPartnerInput::ANYNOMOUS_FALSE);
                    if (!empty($states) && in_array($data_ami['state'], $states)) {
                        array_push($array, $data_ami);
                    }
                    if (empty($states)) {
                        array_push($array, $data_ami);
                    }
                }
            }
            $datas['data'] = $array;
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
            $logo  = AppSetting::getByKey(AppSetting::LOGO, $all_settings);
            $detail->logo ? $detail->logo_default = 0 : $detail->logo_default = 1;
            $detail->logo ? $detail->logo = $image_domain . $detail->logo : $detail->logo = $image_domain . $logo;
            $detail->background ? $detail->background = $image_domain . $detail->background : null;
            $detail->real_end_time ? $detail->real_end_time = date_format(date_create($detail->real_end_time), 'm-d-Y') : null;
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
                'survey_id' => 'string|exists:App\Models\Survey,id',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $survey_user = Survey::where('active', Survey::ACTIVE)->where('active', Survey::ACTIVE)->find($request->survey_id);
            if ($survey_user->state == Survey::STATUS_COMPLETED) {
                return ClientResponse::responseError('Không được sửa khảo sát này');
            }
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            if ($survey_user->state == Survey::STATUS_DRAFT &&  $request->state) {
                $survey_user->start_time = Carbon::now()->format('Y-m-d H:i:s');
            }
            $request->real_end_time ? $survey_user->real_end_time = FormatDate::formatDate($request->real_end_time) : null;
            $request->description ? $survey_user->description = ucfirst($request->description) : "";
            $request->title ? $survey_user->title = ucfirst($request->title) : "";
            $request->font_size ? $survey_user->font_size = $request->font_size : "";
            $request->letter_font ? $survey_user->letter_font = $request->letter_font : "";
            $request->title_color ? $survey_user->title_color = $request->title_color : "";
            $request->content_color ? $survey_user->content_color = $request->content_color : "";
            $request->button_color ? $survey_user->button_color = $request->button_color : "";
            $request->text_color_of_button ? $survey_user->text_color_of_button = $request->text_color_of_button : "";
            isset($request->background_id) ? $survey_user->background_id = $request->background_id : "";
            $request->state ? $survey_user->state = $request->state : "";
            $request->link_url ? $survey_user->link_url = $request->link_url : "";
            ($request->reset_logo == 1) ? $survey_user->logo = null : "";
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $survey_user->user_id = $user_id;
            $survey_user->updated_by = $user_id;
            $survey_user->updated_at = Carbon::now();
            if ($request->limmit_of_response_anomyous) {
                if (!CheckResponseOfSurvey::checkAllResponseOfSurvey($user_id, $request->limmit_of_response_anomyous) || !CheckResponseOfSurvey::checkResponseSettingOfSurvey($user_id, $request->limmit_of_response_anomyous)) {
                    return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng giới hạn phản hồi đã hết, Vui lòng đăng ký gói cước để có thêm lượt tạo khảo sát');
                }
                $survey_user->limmit_of_response_anomyous = $request->limmit_of_response_anomyous;
            }
            if (isset($request->is_logo)) {
                if (CheckResponseOfSurvey::checkDeleteLogo($user_id)) {
                    return ClientResponse::response(ClientResponse::$add_logo, 'Vui lòng đăng ký gói cước để sử dụng chức năng này');
                }
                $survey_user->is_logo = $request->is_logo;
            }
            if ($survey_user->state == Survey::STATUS_NOT_COMPLETED &&  $request->real_end_time) {
                $survey_user->state = Survey::STATUS_ON_PROGRESS;
                $survey_user->status_not_completed = null;
            }
            $key_notifications = Survey::countSurveyLinkUrlNotNull($user_id);
            $survey_user->save();
            if (isset($request->link_url) && Survey::countSurveyLinkUrlNotNull($user_id) == 3 && $key_notifications < 3) {
                $template_notification = NotificationsFirebase::getTemplateNotification(NotificationsFirebase::PROJECT_NUMBER);
                if ($template_notification) {
                    $input['title'] = $template_notification->title;
                    $input['content'] = $template_notification->content;
                    $input['client_id'] =  $user_id;
                    $input['notification_id'] = $template_notification->id;
                }
                NotificationsFirebaseClients::create($input);
            }
            if (!$survey_user) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $all_settings = AppSetting::getAllSetting();
            $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
            $logo  = AppSetting::getByKey(AppSetting::LOGO, $all_settings);
            if ($survey_user->background_id) {
                $survey_user->background = $image_domain . Images::getDetailImage($survey_user->background_id)->image;
            }
            $survey_user->logo ? $survey_user->logo_default = 0 : $survey_user->logo_default = 1;
            $survey_user->logo ? $survey_user->logo = $image_domain . $survey_user->logo : $survey_user->logo = $image_domain . $logo;
            return ClientResponse::responseSuccess('Update thành công', $survey_user);
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
            SurveyTemplate::deleteAllSurveyTemplate($survey_id);
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
            $survey_detail = Survey::getDetailSurvey($survey_template->survey_id);
            $survey_detail = json_decode(json_encode($survey_detail), true);
            $survey_detail['id'] = CFunction::generateUuid();
            $survey_detail['user_id'] = $user_id;
            $survey_detail['state'] = Survey::STATUS_DRAFT;
            $survey_detail['start_time'] = Carbon::now();
            $survey_detail['title'] = $survey_template->title . '_copy';
            $survey_detail['created_by'] = $user_id;
            $survey_detail = RemoveData::removeUnusedDataCopySurvey($survey_detail);
            $survey = Survey::create($survey_detail);
            if (!$survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $page_id = SurveyQuestion::NO_PAGE;
            $list_questions  = SurveyQuestion::getAllQuestionGroup($survey_template->survey_id, $page_id)->toArray();
            if (count($list_questions) > 0) {
                foreach ($list_questions  as  $list_question) {
                    $list_question = $list_question;
                    $list_question['survey_id'] = $survey->id;
                    self::__copySurveyQuestion($list_question, $survey_template->survey_id);
                }
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
            $survey_detail['state'] = Survey::STATUS_DRAFT;
            $survey_detail['start_time'] = Carbon::now();
            $survey_detail = RemoveData::removeUnusedDataCopySurvey($survey_detail);
            $survey = Survey::create($survey_detail);
            if (!$survey) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $page_id = SurveyQuestion::NO_PAGE;
            $list_questions  = SurveyQuestion::getAllQuestionGroup($survey_id, $page_id)->toArray();
            if (count($list_questions) > 0) {
                foreach ($list_questions  as  $list_question) {
                    $list_question = $list_question;
                    $list_question['survey_id'] = $survey->id;
                    self::__copySurveyQuestion($list_question, $survey_id);
                }
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
            if (count($list_question_groups) > 0) {
                foreach ($list_question_groups as  $value) {
                    $value['page_id'] =  $result->id;
                    $value['survey_id'] =  $survey_question['survey_id'];
                    self::__copyQuestion($value, $value['id']);
                }
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
            $inputs = $request->all();
            foreach ($inputs as $key => $value) {
                $data = [];
                foreach ($value['target_values'] as $target_value) {
                    $input['target_value'] = $target_value;
                    $input['survey_id'] = $survey_id;
                    $input['target_type'] = $value['target_type'];
                    $data[] = $input;
                }
                SurveyTargets::getSurveyTarget($survey_id, $value['target_type'])->delete();
                $create_target = SurveyTargets::insert($data);
                if (!$create_target) {
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
            }
            //$target_survey = SurveyTargets::getSurveyTarget($survey_id)->get()->groupBy('target_type');
            return ClientResponse::responseSuccess('OK');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function deleteTargetSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $target_survey_id = $request->target_survey_id;
            $detail = SurveyTargets::getDetailTargetSurvey($survey_id, $target_survey_id);
            if (!$detail) {
                return ClientResponse::responseError('Không tồn tại bản ghi');
            }
            SurveyTargets::destroy($target_survey_id);
            $target_survey = SurveyTargets::getSurveyTarget($survey_id)->get()->groupBy('target_type');
            return ClientResponse::responseSuccess('Xóa thành công', $target_survey);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function uploadLogo(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'image' => 'required|mimes:jpeg,png,jpg|max:512',
                ],
                [
                    'image.required' => 'File ảnh là bắt buộc.',
                    'image.mimes' => 'Hỗ trợ các định dạng jpeg,png,jpg.',
                    'image.max' => 'Kích thước tối đa 512KB.',
                ]
            );
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            if ($file = $request->file('image')) {
                $name =   md5($file->getClientOriginalName() . rand(1, 9999)) . '.' . $file->extension();
                $time_now = Carbon::now();
                $user_package = UserPackage::getPackageUser($user_id, $time_now);
                if ($user_package['add_logo'] == 1) {
                    $path = env('FTP_PATH') . FtpSv::LOGO_FOLDER;
                    $image = FtpSv::upload($file, $name, $path, FtpSv::LOGO_FOLDER);
                    $survey_user = Survey::where('active', Survey::ACTIVE)->where('active', Survey::ACTIVE)->find($request->survey_id);
                    $survey_user->logo = $image;
                    $survey_user->save();
                    $all_settings = AppSetting::getAllSetting();
                    $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
                    $survey_user->logo ? $survey_user->logo_default = 0 : $survey_user->logo_default = 1;
                    $survey_user->logo = $image_domain . $survey_user->logo;
                    $survey_user->background ? $survey_user->background = $image_domain . $survey_user->background : null;
                    $survey_user->real_end_time ? $survey_user->real_end_time = date_format(date_create($survey_user->real_end_time), 'm-d-Y') : null;
                    if ($survey_user->background_id) {
                        $survey_user->background = $image_domain . Images::getDetailImage($survey_user->background_id)->image;
                    }
                    return ClientResponse::responseSuccess('OK',  $survey_user);
                } else {
                    return ClientResponse::response(ClientResponse::$add_logo, 'Vui lòng đăng ký gói cước để sử dụng chức năng này');
                }
            }
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
