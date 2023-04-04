<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\FormatDate;
use App\Helpers\RemoveData;
use App\Models\AcademicLevel;
use App\Models\FamilyIncomeLevels;
use App\Models\Gender;
use App\Models\Province;
use App\Models\QuestionType;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
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
            $filter['is_anynomous'] = $request->is_anynomous ?? null;
            $filter['start_time'] = FormatDate::formatDate($request->start_time) ?? null;
            $filter['end_time'] = FormatDate::formatDate($request->end_time . '23:59:59') ?? null;
            $filter['gender'] = $request->gender ?? null;
            $filter['year_of_birth'] = $request->year_of_birth ?? null;
            $filter['province_codes'] = $request->province_code ?? null;
            $filter['academic_level_ids'] = $request->academic_level_id ?? null;
            $filter['family_income_level_ids'] = $request->family_income_level_id ?? null;
            $filter['job_type_ids'] = $request->job_type_id ?? null;
            $filter['marital_status_ids'] = $request->marital_status_id ?? null;
            $filter['family_peoples'] = $request->family_people ?? null;
            $filter['has_children'] = $request->has_children ?? null;
            $filter['is_key_shopper'] = $request->is_key_shopper ?? null;
            $result = SurveyPartnerInput::getDiagramSurvey($survey_id, $filter);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            switch ($group_by) {
                case 'province_name':
                    $list = Province::getAllProvince();
                    break;
                case 'gender_name':
                    $list = Gender::getAllGender();
                    break;
                case 'academic_level_name':
                    $list = AcademicLevel::getAllAcademicLevel();

                    break;
                case 'personal_income_level_name':
                    $list = FamilyIncomeLevels::getAllFamilyIncomeLevels();
                    break;
                default:
                    return ClientResponse::responseError('Group By không hợp lệ', $group_by);
                    break;
            }
            $data = $result->groupBy($group_by);
            $array = [];
            $default = array();
            foreach ($list as $key => $value) {
                $default[$value['name']] = ['value_group_by' => $value['name'], 'total' => 0];
            }
            foreach ($data as $key => $item) {
                $array['total'] = count($item);
                $array['value_group_by'] = $key;
                if (array_key_exists($key, $default)) {
                    $default[$key] = $array;
                }
            }
            $default = collect($default)->sortByDesc('total')->take($limit);
            return ClientResponse::responseSuccess('Ok', $default);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getDiagramYearOfBirth(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $limit = $request->limit;
            $filter['is_anynomous'] = $request->is_anynomous ?? null;
            $filter['start_time'] = FormatDate::formatDate($request->start_time) ?? null;
            $filter['end_time'] = FormatDate::formatDate($request->end_time . '23:59:59') ?? null;
            $filter['gender'] = $request->gender ?? null;
            $filter['year_of_birth'] = $request->year_of_birth ?? null;
            $filter['province_codes'] = $request->province_code ?? null;
            $filter['academic_level_ids'] = $request->academic_level_id ?? null;
            $filter['family_income_level_ids'] = $request->family_income_level_id ?? null;
            $filter['job_type_ids'] = $request->job_type_id ?? null;
            $filter['marital_status_ids'] = $request->marital_status_id ?? null;
            $filter['family_peoples'] = $request->family_people ?? null;
            $filter['has_children'] = $request->has_children ?? null;
            $filter['is_key_shopper'] = $request->is_key_shopper ?? null;
            $list_year_of_birth = YearOfBirths::getAllYearOfBirth();
            $data = [];
            foreach ($list_year_of_birth as $value) {
                $year_max = Carbon::now()->year  - $value['min_value'];
                $year_min = Carbon::now()->year - $value['max_value'];
                $result = SurveyPartnerInput::getDiagramYearOfBirth($survey_id, $year_min, $year_max, $filter);
                $arr['year_of_birth_name'] = $value['name'];
                $arr['total'] =  $result->count();
                $data[$value['name']] = $arr;
            }
            if (!$data) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $data = collect($data)->sortByDesc('total')->take($limit);
            return ClientResponse::responseSuccess('Ok', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getStatisticQuestionsSurvey(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 20;
            $page = $request->current_page ?? 1;
            $survey_id = $request->survey_id;
            $filter['is_anynomous'] = $request->is_anynomous ?? null;
            $filter['start_time'] = FormatDate::formatDate($request->start_time) ?? null;
            $filter['end_time'] = FormatDate::formatDate($request->end_time . '23:59:59') ?? null;
            $filter['gender'] = $request->gender ?? null;
            $filter['year_of_birth'] = $request->year_of_birth ?? null;
            $filter['province_codes'] = $request->province_code ?? null;
            $filter['academic_level_ids'] = $request->academic_level_id ?? null;
            $filter['family_income_level_ids'] = $request->family_income_level_id ?? null;
            $filter['job_type_ids'] = $request->job_type_id ?? null;
            $filter['marital_status_ids'] = $request->marital_status_id ?? null;
            $filter['family_peoples'] = $request->family_people ?? null;
            $filter['has_children'] = $request->has_children ?? null;
            $filter['is_key_shopper'] = $request->is_key_shopper ?? null;
            $datas = SurveyQuestion::getListQuestion($survey_id, $perPage, $page, $logic_comes = null);
            $datas = RemoveData::removeUnusedData($datas);
            if (!$datas) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            $question = [];
            foreach ($datas['data'] as $key => $value) {
                if ($value->question_type == QuestionType::GROUP) {

                    $question_group = SurveyQuestion::listGroupQuestions($survey_id, $value->id);
                    $list_question = [];
                    foreach ($question_group as $cat => $item) {
                        $list_question[$cat] = self::__getDataQuestions($item, $survey_id, $list_question, $filter);
                    }
                    $value->group_question = $list_question;
                    $datas['data'][$key] = $value;
                } else {
                    $datas['data'][$key] = self::__getDataQuestions($value, $survey_id, $question, $filter);
                }
            }
            return ClientResponse::responseSuccess('Ok', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    private static function __getDataQuestions($value, $survey_id, $question, $filter)
    {
        $query = SurveyPartnerInput::getStatisticQuestionsSurvey(
            $survey_id,
            $value->id,
            $filter
        );
        $question = $value;
        $group = $query->get()->groupBy('skipped');
        $question->number_of_response =  array_key_exists(SurveyPartnerInputLine::NOT_SKIP, json_decode($group, true)) ? count($group[SurveyPartnerInput::NOT_SKIP]->groupBy('partner_input_id')) : 0;
        $question->number_of_skip = array_key_exists(SurveyPartnerInputLine::SKIP, json_decode($group, true)) ? count($group[SurveyPartnerInput::SKIP]->groupBy('partner_input_id')) : 0;
        $question->view =  $question->number_of_response + $question->number_of_skip;
        return $question;
    }

    public function getStatisticSurvey(Request $request)
    {
        try {
            $survey_id = $request->survey_id;
            $filter['is_anynomous'] = $request->is_anynomous ?? null;
            $filter['start_time'] = FormatDate::formatDate($request->start_time) ?? null;
            $filter['end_time'] = FormatDate::formatDate($request->end_time . '23:59:59') ?? null;
            $filter['gender'] = $request->gender ?? null;
            $filter['year_of_birth'] = $request->year_of_birth ?? null;
            $filter['province_codes'] = $request->province_code ?? null;
            $filter['academic_level_ids'] = $request->academic_level_id ?? null;
            $filter['family_income_level_ids'] = $request->family_income_level_id ?? null;
            $filter['job_type_ids'] = $request->job_type_id ?? null;
            $filter['marital_status_ids'] = $request->marital_status_id ?? null;
            $filter['family_peoples'] = $request->family_people ?? null;
            $filter['has_children'] = $request->has_children ?? null;
            $filter['is_key_shopper'] = $request->is_key_shopper ?? null;
            $survey_detail = Survey::getDetailSurveyStatistic($survey_id);
            if (!$survey_detail) {
                return ClientResponse::responseError('Không có bản ghi nào phù hợp');
            }
            $query = SurveyPartnerInput::getStatisticSurvey($survey_id, $filter);
            if ($query->count() == 0) {
                $survey_detail['number_of_response'] =  0;
                $survey_detail['number_of_skip'] = 0;
                $survey_detail['completion_rate'] = 0;
                $survey_detail['average_time'] = '0';
                return ClientResponse::responseSuccess('Ok', $survey_detail);
            }
            $number_of_response = $query->get()->groupBy('state');
            //$number_of_skip = $query->get()->groupBy('skip');
            //dd($number_of_response, $number_of_skip);
            $survey_detail['number_of_response'] =  array_key_exists(SurveyPartnerInput::STATUS_DONE, json_decode($number_of_response, true)) ? count($number_of_response[SurveyPartnerInput::STATUS_DONE]) : 0;
            //$survey_detail['number_of_skip'] = array_key_exists(SurveyPartnerInput::SKIP, json_decode($number_of_skip, true)) ? count($number_of_skip[SurveyPartnerInput::SKIP]) : 0;
            $survey_detail['number_of_skip'] = array_key_exists(SurveyPartnerInput::STATUS_NEW, json_decode($number_of_response, true)) ? count($number_of_response[SurveyPartnerInput::STATUS_NEW]) : 0;
            ($survey_detail['number_of_response'] + $survey_detail['number_of_skip']) != 0 ? $completion_rate = ($survey_detail['number_of_response'] / ($survey_detail['number_of_response'] + $survey_detail['number_of_skip'])) * 100 : $completion_rate = 0;
            $survey_detail['completion_rate'] = round($completion_rate, 2);
            $query = $query->where('survey_partner_inputs.state', SurveyPartnerInput::STATUS_DONE);
            $average_time = ($query->avg('end_datetime') - $query->avg('start_datetime'));
            $hours = floor(($average_time) / (60 * 60));
            $minutes = floor(($average_time  - $hours * 60 * 60) / 60);
            $seconds = floor(($average_time  - $hours * 60 * 60 - $minutes * 60));
            $survey_detail['average_time'] = $hours . ":" . $minutes . ":" . $seconds;
            $survey_detail['view'] = ($survey_detail['number_of_response'] + $survey_detail['number_of_skip']) ?? 0;
            return ClientResponse::responseSuccess('Ok', $survey_detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getSurveyStatisticDetail(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $survey_id = $request->survey_id;
            $question_id = $request->question_id;
            $filter['is_anynomous'] = $request->is_anynomous ?? null;
            $filter['start_time'] = FormatDate::formatDate($request->start_time) ?? null;
            $filter['end_time'] = FormatDate::formatDate($request->end_time . '23:59:59') ?? null;
            $filter['gender'] = $request->gender ?? null;
            $filter['year_of_birth'] = $request->year_of_birth ?? null;
            $filter['province_codes'] = $request->province_code ?? null;
            $filter['academic_level_ids'] = $request->academic_level_id ?? null;
            $filter['family_income_level_ids'] = $request->family_income_level_id ?? null;
            $filter['job_type_ids'] = $request->job_type_id ?? null;
            $filter['marital_status_ids'] = $request->marital_status_id ?? null;
            $filter['family_peoples'] = $request->family_people ?? null;
            $filter['has_children'] = $request->has_children ?? null;
            $filter['is_key_shopper'] = $request->is_key_shopper ?? null;
            $list = '';
            $chart = '';
            $survey_questions = SurveyQuestion::getDetailSurveyQuestion($question_id);

            $question_type = $survey_questions->question_type;
            switch ($question_type) {
                case QuestionType::YES_NO:
                case QuestionType::MULTI_CHOICE:
                case QuestionType::MULTI_CHOICE_DROPDOWN:
                    $data =  SurveyPartnerInput::getSurveyStatisticCheckbox($question_id, $survey_id, $filter);
                    $chart = $data;
                    break;
                case QuestionType::RATING_STAR:
                    $data =  SurveyPartnerInput::getSurveyStatisticRating($question_id, $survey_id, $filter);
                    $chart = $data;
                    break;
                case QuestionType::RANKING:
                    $data =  SurveyPartnerInput::getSurveyStatisticRanking($question_id, $survey_id, $filter);
                    $chart = $data;
                    break;
                case QuestionType::DATETIME_DATE:
                case QuestionType::DATETIME_DATE_RANGE:
                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                case QuestionType::NUMBER:
                    $data =  SurveyPartnerInput::getSurveyStatisticTextOrDate($perPage, $page,  $question_id, $survey_id, $question_type, $filter, $survey_questions->is_time);
                    $list = $data;
                    break;
                case QuestionType::MULTI_FACTOR_MATRIX:
                    $data =  SurveyPartnerInput::getSurveyStatisticMatrix($question_id, $survey_id, $filter);
                    $chart = $data;
                    break;
                default:
                    return ClientResponse::responseError('question type không hợp lệ', $question_type);
                    break;
            }
            $query = SurveyPartnerInput::getStatisticQuestionsSurvey(
                $survey_id,
                $question_id,
                $filter
            );
            $group = $query->get()->groupBy('skipped');
            $number_of_response =  array_key_exists(SurveyPartnerInputLine::NOT_SKIP, json_decode($group, true)) ? count($group[SurveyPartnerInput::NOT_SKIP]->groupBy('partner_input_id')) : 0;
            $number_of_skip = array_key_exists(SurveyPartnerInputLine::SKIP, json_decode($group, true)) ? count($group[SurveyPartnerInput::SKIP]->groupBy('partner_input_id')) : 0;
            $result = [
                'question_name' => $survey_questions->title,
                'description' => $survey_questions->description,
                'sequence' => $survey_questions->sequence,
                'question_type' => $question_type,
                'number_of_response' => $number_of_response,
                'number_of_skip' => $number_of_skip,
                'chart' => $chart,
                'list_values' => $list,
            ];
            return ClientResponse::responseSuccess('OK', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function exportFileSurvey(Request $request)
    {
        try {
            $arr_export = [];
            $arr_heading = ['Mã lượt hỏi'];
            $survey_id = $request->survey_id;
            $request->data_from == Survey::AMI ? $is_anynomous = SurveyPartnerInput::ANYNOMOUS_FALSE : $is_anynomous = SurveyPartnerInput::ANYNOMOUS_TRUE;
            $datas = SurveyQuestion::getListQuestionExportFile($survey_id);
            foreach ($datas as $key => $value) {
                if ($value->question_type == QuestionType::GROUP) {
                    $group_question = SurveyQuestion::listGroupQuestions($survey_id, $value->id, null);
                    foreach ($group_question as $cat => $item) {
                        $title = $value->title . '-' . $item->title;
                        array_push($arr_heading, $title);
                    }
                } else {
                    if ($value->question_type == QuestionType::MULTI_FACTOR_MATRIX) {
                        $list_answer = SurveyQuestionAnswer::getListAnswer($value->id);
                        foreach ($list_answer as $cat => $item) {
                            $title = $value->title . '-' . $item->value;
                            array_push($arr_heading, $title);
                        }
                    } else {
                        $title = $value->title;
                        array_push($arr_heading, $title);
                    }
                }
            }
            array_push($arr_export, $arr_heading);
            $list_input = SurveyPartnerInput::listInput($survey_id, $is_anynomous);
            foreach ($list_input as $key => $value) {
                $arr_row = [];
                array_push($arr_row, $value->id);
                $datas = SurveyQuestion::getListQuestionExportFile($survey_id);
                foreach ($datas as $cat => $item) {
                    if ($item->question_type == QuestionType::GROUP) {
                        $group_question = SurveyQuestion::listGroupQuestions($survey_id, $item->id, null);
                        foreach ($group_question as $k => $v) {
                            $data = self::__getInputLine($v, $arr_row);
                            array_push($arr_row,  $data);
                        }
                    } else {
                        if ($item->question_type == QuestionType::MULTI_FACTOR_MATRIX) {
                            $list_answer = SurveyQuestionAnswer::getListAnswer($item->id);
                            foreach ($list_answer as  $v) {
                                $list_input_line = SurveyPartnerInputLine::listInputLine($value->id, $item->id, $v['id']);
                                $data = '';
                                foreach ($list_input_line as $key =>  $detail) {
                                    $answer = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($detail->matrix_column_id);
                                    if ($key == 0) {
                                        $answer ? $data =   $data . $answer->value : $data =  '';
                                    } else {
                                        $answer ? $data =  $data . ', ' . $answer->value : $data =  '';
                                    }
                                }
                                array_push($arr_row,  $data);
                            }
                        } else {
                            $data = self::__getInputLine($item, $arr_row);
                            array_push($arr_row,  $data);
                        }
                    }
                }
                array_push($arr_export,  $arr_row);
            }
            return ClientResponse::responseSuccess('OK', $arr_export);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    private function __getInputLine($value, $arr_row)
    {
        $data = '';
        switch ($value->question_type) { // question_id 
            case QuestionType::MULTI_CHOICE:
            case QuestionType::MULTI_CHOICE_DROPDOWN:
            case QuestionType::YES_NO:
                $list_input_line = SurveyPartnerInputLine::listInputLine($arr_row[0], $value->id);
                foreach ($list_input_line as $key =>  $detail) {
                    $answer = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($detail->suggested_answer_id);
                    if ($key == 0) {
                        $answer ? $data =   $data . $answer->value : $data =  '';
                    } else {
                        $answer ? $data =  $data  . ', ' .  $answer->value : $data =  '';
                    }
                }
                break;
                break;
            case QuestionType::DATETIME_DATE:
                $list_input_line = SurveyPartnerInputLine::listInputLine($arr_row[0], $value->id);
                foreach ($list_input_line as  $detail) {
                    if ($value->is_date == 1) {
                        $detail->value_date ? $data =  date_format(date_create($detail->value_date), $value->format_date_time) : $data =  '';
                    } else {
                        $detail->value_date ? $data =  $detail->value_date : $data =  '';
                    }
                }
                break;
            case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                $list_input_line = SurveyPartnerInputLine::listInputLine($arr_row[0], $value->id);
                foreach ($list_input_line as  $detail) {
                    $detail->value_text_box ? $data =  $detail->value_text_box : $data =  '';
                }
                break;
            case QuestionType::QUESTION_ENDED_LONG_TEXT:
                $list_input_line = SurveyPartnerInputLine::listInputLine($arr_row[0], $value->id);
                foreach ($list_input_line as  $detail) {
                    $detail->value_char_box ? $data =  $detail->value_char_box : $data =  '';
                }
                break;
            case QuestionType::NUMBER:
                $list_input_line = SurveyPartnerInputLine::listInputLine($arr_row[0], $value->id);
                foreach ($list_input_line as  $detail) {
                    $detail->value_number ? $data =  $detail->value_number : $data =  '';
                }
                break;
            case QuestionType::RATING_STAR:
            case QuestionType::RANKING:
                $list_input_line = SurveyPartnerInputLine::listInputLine($arr_row[0], $value->id);
                foreach ($list_input_line as  $detail) {
                    $detail->value_rating_ranking ? $data =  $detail->value_rating_ranking : $data =  '';
                }
                break;
        }
        return $data;
    }
}
