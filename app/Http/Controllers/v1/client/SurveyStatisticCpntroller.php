<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\RemoveData;
use App\Models\QuestionType;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

class SurveyStatisticCpntroller extends Controller
{
    public function getDiagramSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $group_by = $request->group_by;
            $limit = $request->limit;
            $start_time = $request->start_time;
            $end_time = $request->end_time;
            $gender = $request->gender;
            $province_codes = $request->province_codes;
            $academic_level_ids = $request->academic_level_ids;
            $job_type_ids = $request->job_type_ids;
            $marital_status_ids = $request->marital_status_ids;
            $family_peoples = $request->family_peoples;
            $has_children = $request->has_children;
            $is_key_shopper = $request->is_key_shopper;
            $result = SurveyPartnerInputLine::getDiagramSurvey(
                $survey_id,
                $start_time,
                $end_time,
                $academic_level_ids,
                $province_codes,
                $gender,
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

    public function getStatisticSurvey(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 5;
            $page = $request->current_page ?? 1;
            $survey_id = $request->survey_id;
            $datas = SurveyQuestion::getListQuestion($survey_id, $perPage,  $page);
            $datas = RemoveData::removeUnusedData($datas);
            if (!$datas) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Ok', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getSurveyDetail(Request $request)
    {
        try {
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
                case QuestionType::MULTI_CHOICE_CHECKBOX:
                case QuestionType::YES_NO:
                case QuestionType::MULTI_CHOICE_RADIO:
                case QuestionType::MULTI_CHOICE_DROPDOWN:
                    $data =  SurveyPartnerInputLine::getSurveyStatisticCheckbox($question_id, $survey_id, $is_anynomous);
                    $chart = $data;
                    break;
                case QuestionType::RATING_STAR:
                    $data =  SurveyPartnerInputLine::getSurveyStatisticRating($question_id, $survey_id, $is_anynomous);
                    $chart = $data;
                    break;
                case QuestionType::DATETIME_DATE:
                case QuestionType::DATETIME_DATE_RANGE:
                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                    $data =  SurveyPartnerInputLine::getSurveyStatisticTextOrDate($perPage, $page,  $question_id, $survey_id, $question_type, $is_anynomous);
                    $list = $data;
                    return $list;
                    break;
                case QuestionType::MULTI_FACTOR_MATRIX:
                    $data =  SurveyPartnerInputLine::getSurveyStatisticMatrix($question_id, $survey_id, $is_anynomous);
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
