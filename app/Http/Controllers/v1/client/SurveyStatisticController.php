<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\FormatDate;
use App\Helpers\RemoveData;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;
use App\Models\YearOfBirths;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SurveyStatisticController extends Controller
{
    public function getDiagramSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $group_by = $request->group_by;
            $limit = $request->limit;
            $start_time = FormatDate::formatDate($request->start_time);
            $end_time = FormatDate::formatDate($request->end_time);
            $gender = $request->gender;
            $year_of_birth = $request->year_of_birth;
            $province_codes = $request->province_codes;
            $academic_level_ids = $request->academic_level_ids;
            $job_type_ids = $request->job_type_ids;
            $marital_status_ids = $request->marital_status_ids;
            $family_peoples = $request->family_peoples;
            $has_children = $request->has_children;
            $is_key_shopper = $request->is_key_shopper;
            $result = SurveyPartnerInput::getDiagramSurvey(
                $survey_id,
                $start_time,
                $end_time,
                $academic_level_ids,
                $province_codes,
                $gender,
                $year_of_birth,
                $job_type_ids,
                $marital_status_ids,
                $family_peoples,
                $has_children,
                $is_key_shopper
            );
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $data = $result->groupBy($group_by);
            $array = [];
            foreach ($data as $key => $item) {
                $array['total'] = count($item);
                $array['value_group_by'] = $key;
                $data[$key] = $array;
            }
            $data = $data->sortByDesc('total')->take($limit);
            return ClientResponse::responseSuccess('Ok', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getDiagramYearOfBirth(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $start_time = FormatDate::formatDate($request->start_time);
            $end_time = FormatDate::formatDate($request->end_time);
            $gender = $request->gender;
            $year_of_birth = $request->year_of_birth;
            $province_codes = $request->province_codes;
            $academic_level_ids = $request->academic_level_ids;
            $job_type_ids = $request->job_type_ids;
            $marital_status_ids = $request->marital_status_ids;
            $family_peoples = $request->family_peoples;
            $has_children = $request->has_children;
            $is_key_shopper = $request->is_key_shopper;
            $list_year_of_birth = YearOfBirths::getAllYearOfBirth();
            $data = [];
            foreach ($list_year_of_birth as $value) {
                $year_max = Carbon::now()->year  - $value['min_value'];
                $year_min = Carbon::now()->year - $value['max_value'];
                $result = SurveyPartnerInput::getDiagramYearOfBirth(
                    $survey_id,
                    $year_min,
                    $year_max,
                    $start_time,
                    $end_time,
                    $academic_level_ids,
                    $province_codes,
                    $gender,
                    $year_of_birth,
                    $job_type_ids,
                    $marital_status_ids,
                    $family_peoples,
                    $has_children,
                    $is_key_shopper
                );
                $arr['year_of_birth_name'] = $value['name'];
                $arr['totle'] =  $result->count();
                $data[$value['name']] = $arr;
            }
            if (!$data) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Ok', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getStatisticQuestionsSurvey(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 5;
            $page = $request->current_page ?? 1;
            $survey_id = $request->survey_id;
            $is_anynomous = $request->is_anynomous;
            $start_time = FormatDate::formatDate($request->start_time);
            $end_time = FormatDate::formatDate($request->end_time);
            $gender = $request->gender;
            $year_of_birth = $request->year_of_birth;
            $province_codes = $request->province_codes;
            $academic_level_ids = $request->academic_level_ids;
            $job_type_ids = $request->job_type_ids;
            $marital_status_ids = $request->marital_status_ids;
            $family_peoples = $request->family_peoples;
            $has_children = $request->has_children;
            $is_key_shopper = $request->is_key_shopper;
            $datas = SurveyQuestion::getListQuestion($survey_id, $perPage, $page);
            $datas = RemoveData::removeUnusedData($datas);
            if (!$datas) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $question = [];
            foreach ($datas['data'] as $key => $value) {
                $query = SurveyPartnerInput::getStatisticQuestionsSurvey(
                    $survey_id,
                    $value['id'],
                    $is_anynomous,
                    $start_time,
                    $end_time,
                    $academic_level_ids,
                    $province_codes,
                    $gender,
                    $year_of_birth,
                    $job_type_ids,
                    $marital_status_ids,
                    $family_peoples,
                    $has_children,
                    $is_key_shopper
                );
                $question = $value;
                $group = $query->get()->groupBy('skipped');
                $question['number_of_response'] =  array_key_exists(SurveyPartnerInputLine::NOT_SKIP, json_decode($group, true)) ? count($group[SurveyPartnerInput::NOT_SKIP]->groupBy('partner_id')) : 0;
                $question['number_of_skip'] = array_key_exists(SurveyPartnerInputLine::SKIP, json_decode($group, true)) ? count($group[SurveyPartnerInput::SKIP]->groupBy('partner_id')) : 0;
                $question['view'] =  $question['number_of_response'] + $question['number_of_skip'];
                $datas['data'][$key] = $question;
            }
            return ClientResponse::responseSuccess('Ok', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getStatisticSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $is_anynomous = $request->is_anynomous;
            $start_time = FormatDate::formatDate($request->start_time);
            $end_time = FormatDate::formatDate($request->end_time . '' . '23:59:59');
            $gender = $request->gender;
            $year_of_birth = $request->year_of_birth;
            $province_codes = $request->province_codes;
            $academic_level_ids = $request->academic_level_ids;
            $job_type_ids = $request->job_type_ids;
            $marital_status_ids = $request->marital_status_ids;
            $family_peoples = $request->family_peoples;
            $has_children = $request->has_children;
            $is_key_shopper = $request->is_key_shopper;
            $survey_detail = Survey::getDetailSurveyStatistic($survey_id);
            if (!$survey_detail) {
                return ClientResponse::responseError('Không có bản ghi nào phù hợp');
            }
            $query = SurveyPartnerInput::getStatisticSurvey(
                $survey_id,
                $is_anynomous,
                $start_time,
                $end_time,
                $academic_level_ids,
                $province_codes,
                $gender,
                $year_of_birth,
                $job_type_ids,
                $marital_status_ids,
                $family_peoples,
                $has_children,
                $is_key_shopper
            );
            if ($query->count() == 0) {
                $survey_detail['number_of_response'] =  0;
                $survey_detail['number_of_skip'] = 0;
                $survey_detail['completion_rate'] = 0;
                $survey_detail['average_time'] = '0';
                return ClientResponse::responseSuccess('Ok', $survey_detail);
            }
            $number_of_response = $query->get()->groupBy('state');
            $number_of_skip = $query->get()->groupBy('skip');
            $survey_detail['number_of_response'] =  array_key_exists(SurveyPartnerInput::DONE, json_decode($number_of_response, true)) ? count($number_of_response[SurveyPartnerInput::DONE]) : 0;
            $survey_detail['number_of_skip'] = array_key_exists(SurveyPartnerInput::SKIP, json_decode($number_of_skip, true)) ? count($number_of_skip[SurveyPartnerInput::SKIP]) : 0;
            $completion_rate = ($survey_detail['number_of_response'] / ($survey_detail['number_of_response'] + $survey_detail['number_of_skip'])) * 100;
            $survey_detail['completion_rate'] = round($completion_rate, 2);
            $average_time = ($query->avg('end_datetime') - $query->avg('start_datetime'));
            $hours = floor(($average_time) / (60 * 60));
            $minutes = floor(($average_time  - $hours * 60 * 60) / 60);
            $seconds = floor(($average_time  - $hours * 60 * 60 - $minutes * 60));
            $survey_detail['average_time'] = $hours . ":" . $minutes . ":" . $seconds;
            return ClientResponse::responseSuccess('Ok', $survey_detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getSurveyStatisticDetail(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 5;
            $page = $request->current_page ?? 1;
            $is_anynomous = $request->is_anynomous;
            $survey_id = $request->survey_id;
            $question_id = $request->question_id;
            $list = '';
            $chart = '';
            $survey_questions = SurveyQuestion::getDetailSurveyQuestion($question_id);
            $question_type = $survey_questions->question_type;
            switch ($question_type) {
                case QuestionType::YES_NO:
                case QuestionType::MULTI_CHOICE:
                case QuestionType::MULTI_CHOICE_DROPDOWN:
                    $data =  SurveyPartnerInput::getSurveyStatisticCheckbox($question_id, $survey_id, $is_anynomous);
                    $chart = $data;
                    break;
                case QuestionType::RATING_STAR:
                    $data =  SurveyPartnerInput::getSurveyStatisticRating($question_id, $survey_id, $is_anynomous);
                    $chart = $data;
                    break;
                case QuestionType::DATETIME_DATE:
                case QuestionType::DATETIME_DATE_RANGE:
                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                    $data =  SurveyPartnerInput::getSurveyStatisticTextOrDate($perPage, $page,  $question_id, $survey_id, $question_type, $is_anynomous);
                    $list = $data;
                    return $list;
                    break;
                case QuestionType::MULTI_FACTOR_MATRIX:
                    $data =  SurveyPartnerInput::getSurveyStatisticMatrix($question_id, $survey_id, $is_anynomous);
                    $chart = $data;
                    break;
                default:
                    return ClientResponse::responseError('question type không hợp lệ', $question_type);
                    break;
            }
            $result = [
                'question_name' => $survey_questions->title,
                'sequence' => $survey_questions->sequence,
                'question_type' => $question_type,
                'chart' => $chart,
                'list_values' => $list,
            ];
            return ClientResponse::responseSuccess('OK', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function exportSurveyStatistic(Request $request)
    {
        try {
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
