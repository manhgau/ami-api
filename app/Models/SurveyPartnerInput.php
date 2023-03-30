<?php

namespace App\Models;

use App\Helpers\FormatDate;
use App\Helpers\RemoveData;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SurveyPartnerInput extends Model
{
    protected $fillable = [
        'survey_id',
        'partner_id',
        'start_datetime',
        'end_datetime',
        'deadline',
        'state',
        'phone',
        'fullname',
        'is_answer',
        'is_anynomous',
        'a_partner_id',
        'ip',
        'os',
        'browser',
        'user_agent',
        'deleted',
        'created_at',
        'updated_at',
    ];
    const STATUS_NEW        = 'new';
    const STATUS_DONE       = 'done';
    const SKIP              = 1;
    const NOT_SKIP          = 0;

    const CLOSED            = 'closed';
    const COMPLETED         = 'completed';
    const NOT_COMPLETED     = 'not_completed';

    const PARTNER           = 'partner';
    const OTHER             = 'other';

    const ANYNOMOUS_TRUE    = "1";
    const ANYNOMOUS_FALSE   = "0";

    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function updateSurveyPartnerInput($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->update($data);
    }

    public static  function getListSurveyPartnerInputDelete($survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('is_anynomous', self::ANYNOMOUS_TRUE)->where('survey_id', $survey_id)->get();
    }

    public static  function deleteSurveyPartnerInput($survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('is_anynomous', self::ANYNOMOUS_TRUE)->where('survey_id', $survey_id)->update(['deleted' => self::DELETED]);
    }

    public static  function listInput($survey_id, $is_anynomous)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('is_anynomous', $is_anynomous)->where('state', self::STATUS_DONE)->get();
    }

    public static  function countSurveyInput($survey_id, $is_anynomous = null)
    {
        $query =  self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('state', self::STATUS_DONE);
        if ($is_anynomous != null) {
            $query = $query->where('is_anynomous', $is_anynomous);
        }
        return $query->count();
    }

    public static  function countSurveyPartnerInput($survey_id, $partner_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('partner_id', $partner_id)->where('state', self::STATUS_DONE)->count();
    }

    public static  function countAllSurveyUserInput($user_id)
    {
        return  DB::table('survey_partner_inputs')
            ->join('surveys', 'surveys.id', '=', 'survey_partner_inputs.survey_id')
            ->where('surveys.user_id', $user_id)
            ->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_inputs.is_anynomous', self::ANYNOMOUS_TRUE)->count();
    }

    public static  function countPartnerInput($partner_id, $status)
    {
        $query =  DB::table('survey_partners as c')
            ->join('surveys as b', 'b.id', '=', 'c.survey_id')
            //->join('survey_partner_inputs as a', 'a.survey_id', '=', 'b.id')
            ->select(
                'c.id as survey_partner_id',
                'b.title',
                'c.is_save',
                'c.id as survey_partner_id',
                'b.id as survey_id',
                'b.point',
                'b.state_ami',
                'b.start_time',
                'b.end_time',
                'b.question_count as count_questions',
                'b.view',
                'b.created_at',
                'b.attempts_limit_min',
                'b.attempts_limit_max',
                'b.is_answer_single',
                'c.number_of_response_partner',
                'b.limmit_of_response',
                'b.number_of_response',
            )
            ->where('c.stattus', SurveyPartner::STATUS_INACTIVE)
            ->where('c.partner_id', $partner_id)
            ->orderBy('b.created_at', 'desc')
            ->distinct();
        if ($status == self::NOT_COMPLETED) {
            $query->where('b.state_ami', Survey::STATUS_ON_PROGRESS)->whereColumn('c.number_of_response_partner', '<', 'b.attempts_limit_max');
        }
        return $query->count();
    }

    public static  function getALLSurveyPartnerInput($survey_id,  $question_id, $partner_id)
    {
        return  DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->where('survey_partner_inputs.partner_id', $partner_id)
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_input_lines.question_id', $question_id);
    }


    public static  function getlistSurveyPartnerInput($perPage = 10,  $page = 1, $partner_id, $time_now, $time_end, $search = null, $status = null)
    {
        $query =  DB::table('survey_partners as c')
            ->join('surveys as b', 'b.id', '=', 'c.survey_id')
            //->join('survey_partner_inputs as a', 'a.survey_id', '=', 'b.id')
            ->select(
                'c.id as survey_partner_id',
                'b.title',
                'c.is_save',
                'c.id as survey_partner_id',
                'b.id as survey_id',
                'b.point',
                'b.state_ami',
                'b.start_time',
                'b.end_time',
                'b.question_count as count_questions',
                'b.view',
                'b.created_at',
                'b.attempts_limit_min',
                'b.attempts_limit_max',
                'b.is_answer_single',
                'c.number_of_response_partner',
                'b.limmit_of_response',
                'b.number_of_response',
            )
            ->where('c.stattus', SurveyPartner::STATUS_INACTIVE)
            ->where('b.start_time', '<', $time_now)
            ->where('b.end_time', '>', $time_end)
            ->where('c.partner_id', $partner_id)
            ->orderBy('b.created_at', 'desc')
            ->distinct();
        if ($search != null) {
            $query->where('b.title', 'like', '%' . $search . '%');
        }
        if ($status == self::CLOSED) {
            $query->where('b.end_time', '<', Carbon::now())->whereColumn('c.number_of_response_partner', '<', 'b.attempts_limit_min');
        }
        if ($status == self::COMPLETED) {
            $query->where(function ($query) {
                $query->orwhere(function ($query) {
                    $query->where('b.state_ami', Survey::STATUS_ON_PROGRESS)->whereColumn('c.number_of_response_partner', '>=', 'b.attempts_limit_max');
                });
                $query->orwhere(function ($query) {
                    $query->where('b.end_time', '<', Carbon::now())->whereColumn('c.number_of_response_partner', '>=', 'b.attempts_limit_min');
                });
                // $query->orwhere(function ($query) {
                //     $query->where('b.end_time', '>', Carbon::now())->where('b.is_answer_single', Survey::ANSWER_SINGLE)->where('c.number_of_response_partner', '=', 1);
                // });
            });
        }
        if ($status == self::NOT_COMPLETED) {
            $query->where('b.state_ami', Survey::STATUS_ON_PROGRESS)->whereColumn('c.number_of_response_partner', '<', 'b.attempts_limit_max');
        }
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailSurveyPartnerInput($survey_partner_input_id, $partner_id)
    {
        return DB::table('survey_partners as a')
            ->join('surveys as b', 'b.id', '=', 'a.survey_id')
            ->select(
                'a.id as survey_partner_id',
                'a.is_save',
                'a.number_of_response_partner',
                'b.state_ami',
                'b.title',
                'b.description',
                'b.id as survey_id',
                'b.point',
                'b.start_time',
                'b.end_time',
                'b.question_count as count_questions',
                'b.view',
                'b.attempts_limit_min',
                'b.attempts_limit_max',
                'b.is_answer_single',
            )
            ->where('a.id', $survey_partner_input_id)
            ->where('a.partner_id', $partner_id)
            ->first();
    }

    public static  function checkPartnerInput($partner_id, $survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('partner_id', $partner_id)->where('survey_id', $survey_id)->where('state', self::STATUS_DONE)->where('is_answer', self::PARTNER)->count();
    }

    public static  function getDiagramSurvey($survey_id, $filter)
    {
        $query = DB::table('survey_partner_inputs')
            ->join('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->LeftJoin('provinces', 'provinces.code', '=', 'survey_profile_inputs.province_code')
            ->LeftJoin('genders', 'genders.id', '=', 'survey_profile_inputs.gender')
            ->LeftJoin('academic_levels', 'academic_levels.id', '=', 'survey_profile_inputs.academic_level_id')
            ->LeftJoin('personal_income_levels', 'personal_income_levels.id', '=', 'survey_profile_inputs.personal_income_level_id')
            ->select(
                'provinces.name as province_name',
                'genders.name as gender_name',
                'academic_levels.name as academic_level_name',
                'personal_income_levels.name as personal_income_level_name'
            )
            ->where('survey_partner_inputs.survey_id', $survey_id)
            ->where('survey_partner_inputs.is_anynomous', self::ANYNOMOUS_FALSE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_inputs.state', self::STATUS_DONE);
        $query = self::__filterTarget($query, $filter);
        return $query->get();
    }

    public static  function getDiagramYearOfBirth($survey_id, $year_min, $year_max, $filter)
    {
        $query = DB::table('survey_partner_inputs')
            ->LeftJoin('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->where('survey_partner_inputs.survey_id', $survey_id)
            ->where('survey_partner_inputs.is_anynomous', self::ANYNOMOUS_FALSE)
            ->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->whereYear('year_of_birth', '>=', $year_min)
            ->whereYear('year_of_birth', '<=', $year_max);
        $query = self::__filterTarget($query, $filter);
        return $query->get();
    }

    public static  function getStatisticSurvey($survey_id, $filter)
    {
        $query = DB::table('survey_partner_inputs')
            ->join('surveys', 'surveys.id', '=', 'survey_partner_inputs.survey_id')
            ->leftJoin('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->select(
                'survey_partner_inputs.start_datetime',
                'survey_partner_inputs.end_datetime',
                'survey_partner_inputs.skip',
                'survey_partner_inputs.state'
            )
            //->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_inputs.survey_id', $survey_id);
        $query = self::__filterTarget($query, $filter);
        return $query;
    }

    public static  function getStatisticQuestionsSurvey($survey_id, $question_id, $filter)
    {
        $query = DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->leftJoin('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->select(
                'survey_partner_inputs.partner_id',
                'survey_partner_input_lines.skipped',
                'survey_partner_input_lines.question_id',
                'survey_partner_input_lines.partner_input_id',
            )
            ->where('survey_partner_input_lines.question_id', $question_id)
            //->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_inputs.survey_id', $survey_id);
        $query = self::__filterTarget($query, $filter);
        return $query;
    }

    public static  function getDetailDataRaking()
    {
        return [
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 1,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 2,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 3,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 4,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 5,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 6,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 7,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 8,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 9,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 10,
            ],
        ];
    }

    public static  function getDetailDataRating()
    {
        return [
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 1,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 2,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 3,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 4,
            ],
            [
                'number_partner_answer' => 0,
                'value_rating_ranking' => 5,
            ]
        ];
    }

    public static  function getSurveyStatisticCheckbox($question_id, $survey_id, $filter)
    {
        $query = DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->leftJoin('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->select(DB::raw('count(*) as total , survey_partner_input_lines.suggested_answer_id'))
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.skipped', SurveyPartnerInputLine::NOT_SKIP)
            //->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_input_lines.question_id', $question_id);
        $query = self::__filterTarget($query, $filter);
        $result = $query->groupBy('suggested_answer_id')->get()->toArray();
        $data_results = [];
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $key => $value) {
                $name_answer = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($value->suggested_answer_id);
                $input['suggested_answer_id'] = $value->suggested_answer_id;
                $name_answer ? $input['name_answer'] = $name_answer->value : $input['name_answer'] = '';
                $input['number_partner_answer'] = $value->total;
                if ($name_answer) {
                    $data_results[$key] = $input;
                }
            }
        }
        return $data_results;
    }

    public static  function getSurveyStatisticRating($question_id, $survey_id, $filter)
    {
        $query = DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->leftJoin('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->select(
                'survey_partner_input_lines.value_rating_ranking',
                'survey_partner_input_lines.answer_type',
                'survey_partner_input_lines.question_sequence'
            )
            //->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.skipped', SurveyPartnerInputLine::NOT_SKIP)
            ->where('survey_partner_input_lines.question_id', $question_id);
        $query = self::__filterTarget($query, $filter);
        $result = $query->orderBy('survey_partner_input_lines.value_rating_ranking', 'asc')
            ->get()
            ->groupBy('value_rating_ranking')->toArray();
        $data = self::getDetailDataRating();
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $key => $value) {
                $array['number_partner_answer'] = count($value);
                $array['value_rating_ranking'] = $key;
                $data[$key - 1] = $array;
            }
        }
        return $data;
    }

    public static  function getSurveyStatisticRanking($question_id, $survey_id, $filter)
    {
        $query = DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->leftJoin('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->select(
                'survey_partner_input_lines.value_rating_ranking',
                'survey_partner_input_lines.answer_type',
                'survey_partner_input_lines.question_sequence',
                'survey_partner_input_lines.value_level_ranking'
            )
            //->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.skipped', SurveyPartnerInputLine::NOT_SKIP)
            ->where('survey_partner_input_lines.question_id', $question_id);
        $query = self::__filterTarget($query, $filter);
        $result = $query->orderBy('survey_partner_input_lines.value_rating_ranking', 'asc')
            ->get()->groupBy('value_rating_ranking')->toArray();
        $data = self::getDetailDataRaking();
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $key => $item) {
                $arr['number_partner_answer'] = count($item);
                $arr['value_rating_ranking'] = $key;
                $data[$key - 1] = $arr;
            }
        }

        return $data;
    }


    public static  function getSurveyStatisticTextOrDate($perPage, $page, $question_id, $survey_id, $question_type, $filter, $is_time)
    {
        $query = DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->leftJoin('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->select(
                'survey_partner_input_lines.question_sequence',
                'survey_partner_input_lines.answer_type',
                'survey_partner_input_lines.value_text_box',
                'survey_partner_input_lines.value_char_box',
                'survey_partner_input_lines.value_number',
                'survey_partner_input_lines.value_date',
                'survey_partner_input_lines.value_date_start',
                'survey_partner_input_lines.value_date_end',
                'survey_partner_input_lines.created_at'
            )
            //->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.skipped', SurveyPartnerInputLine::NOT_SKIP)
            ->where('survey_partner_input_lines.question_id', $question_id);
        $query = self::__filterTarget($query, $filter);
        $result = $query->paginate($perPage, "*", "page", $page)->toArray();
        $result =    RemoveData::removeUnusedData($result);
        $data = [];
        if (is_array($result) && count($result) > 0) {
            foreach ($result['data'] as $key => $value) {
                switch ($question_type) {
                    case QuestionType::DATETIME_DATE:
                        if ($is_time == 1) {
                            $input['value'] = FormatDate::formatDateStatistic($value->value_date);
                        } else {
                            $input['value'] = FormatDate::formatDateStatisticNoTime($value->value_date);
                        }
                        $input['created_at'] = FormatDate::formatDateStatistic($value->created_at);
                        $data[$key] = $input;
                        break;
                    case QuestionType::DATETIME_DATE_RANGE:
                        if ($is_time == 1) {
                            $input['value'] = FormatDate::formatDateStatistic($value->value_date_start) . '-' . FormatDate::formatDateStatistic($value->value_date_end);
                        } else {
                            $input['value'] = FormatDate::formatDateStatisticNoTime($value->value_date_start) . '-' . FormatDate::formatDateStatisticNoTime($value->value_date_end);
                        }
                        $input['created_at'] = FormatDate::formatDateStatistic($value->created_at);
                        $data[$key] = $input;
                        break;
                    case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                        $input['value'] = $value->value_text_box;
                        $input['created_at'] = FormatDate::formatDateStatistic($value->created_at);
                        $data[$key] = $input;
                        break;
                    case QuestionType::QUESTION_ENDED_LONG_TEXT:
                        $input['value'] = $value->value_char_box;
                        $input['created_at'] = FormatDate::formatDateStatistic($value->created_at);
                        $data[$key] = $input;
                        break;
                    case QuestionType::NUMBER:
                        $input['value'] = $value->value_number;
                        $input['created_at'] = FormatDate::formatDateStatistic($value->created_at);
                        $data[$key] = $input;
                        break;
                    default:
                        return false;
                        break;
                }
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public static  function getDetailDataRowMatrix($question_id)
    {
        $lis_row = SurveyQuestionAnswer::getAllAnswer($question_id, SurveyQuestionAnswer::ROW);
        $arr = array();
        foreach ($lis_row as $k => $v) {
            $array = array();
            $lis_column = SurveyQuestionAnswer::getAllAnswer($question_id, SurveyQuestionAnswer::COLUMN);
            foreach ($lis_column as $key => $value) {
                $array[$value['value']] = ['name_answer_column' => $value['value'], 'number_partner_answer' => 0];
            }
            $arr[$v['value']] = $array;
        }
        return $arr;
    }

    public static  function getDetailDataColumnMatrix($question_id)
    {
        $lis_column = SurveyQuestionAnswer::getAllAnswer($question_id, SurveyQuestionAnswer::COLUMN);
        $arr = array();
        foreach ($lis_column as $key => $value) {
            $arr[$value['value']] = ['name_answer_column' => $value['value'], 'number_partner_answer' => 0];
        }
        return $arr;
    }



    public static  function getSurveyStatisticMatrix($question_id, $survey_id, $filter)
    {
        $query = DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->leftJoin('survey_profile_inputs', 'survey_profile_inputs.partner_input_id', '=', 'survey_partner_inputs.id')
            ->join('survey_question_answers', 'survey_question_answers.id', '=', 'survey_partner_input_lines.matrix_column_id')
            ->select(
                'survey_partner_input_lines.id as lines_id',
                'survey_partner_input_lines.matrix_row_id',
                'survey_partner_input_lines.matrix_column_id',
                'survey_question_answers.value as name_answer_column'
            )
            //->where('survey_partner_inputs.state', self::STATUS_DONE)
            ->where('survey_partner_inputs.deleted', self::NOT_DELETED)
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.skipped', SurveyPartnerInputLine::NOT_SKIP)
            ->where('survey_partner_input_lines.question_id', $question_id);
        $query = self::__filterTarget($query, $filter);
        $result = $query->get();
        if (!$result) {
            return $result;
        }
        $answers = SurveyQuestionAnswer::getAnswerMatrixRow($question_id);
        $answers_value = [];
        foreach ($answers as $key => $value) {
            $answers_value[$value['id']] = $value['value'];
        }
        foreach ($result as $key => $value) {
            $input = json_decode(json_encode($value), true);
            $input['name_answer_row'] = $answers_value[$value->matrix_row_id];
            $result[$key] = $input;
        }
        $result = $result->groupBy('name_answer_row');
        $data = self::getDetailDataRowMatrix($question_id);
        foreach ($result as $k => $item) {
            $group_item = $item->groupBy('name_answer_column');
            $data_column = self::getDetailDataColumnMatrix($question_id);
            foreach ($group_item as $key => $value) {
                $mang['name_answer_column'] = $key;
                $mang['number_partner_answer'] = count($value);
                $data_column[$key] = $mang;
            }
            $data[$k] = $data_column;
        }
        return $data;
    }

    private static function __filterTarget($query, $filter)
    {
        if (isset($filter['is_anynomous'])) {
            $query->where('survey_partner_inputs.is_anynomous', $filter['is_anynomous']);
        }
        if ($filter['start_time'] != null && $filter['end_time'] != null) {
            $query->whereBetween('survey_partner_inputs.created_at', [$filter['start_time'], $filter['end_time']]);
        }
        if ($filter['gender'] != null) {
            $query->whereIn('survey_profile_inputs.gender', $filter['gender']);
        }
        if ($filter['academic_level_ids'] != null) {
            $query->whereIn('survey_profile_inputs.academic_level_id', $filter['academic_level_ids']);
        }
        if ($filter['province_codes'] != null) {
            $query->whereIn('survey_profile_inputs.province_code',  $filter['province_codes']);
        }
        if ($filter['job_type_ids'] != null) {
            $query->whereIn('survey_profile_inputs.job_type_id', $filter['job_type_ids']);
        }
        if ($filter['marital_status_ids'] != null) {
            $query->whereIn('survey_profile_inputs.marital_status_id',  $filter['marital_status_ids']);
        }
        if ($filter['has_children'] != null) {
            $query->whereIn('survey_profile_inputs.is_key_shopper', $filter['has_children']);
        }
        if ($filter['is_key_shopper'] != null) {
            $query->whereIn('survey_profile_inputs.has_children', $filter['is_key_shopper']);
        }
        if ($filter['family_peoples'] != null) {
            $query->whereIn('survey_profile_inputs.family_people', $filter['family_people']);
        }
        $year_of_birth = $filter['year_of_birth'];
        if ($year_of_birth !== null) {
            $query->where(function ($query) use ($year_of_birth) {
                $time = Carbon::now()->year;
                foreach ($year_of_birth as $value) {
                    $detail = YearOfBirths::getDetail($value);
                    $year_max = Carbon::create($time - $detail->min_value)->format('Y-m-d');
                    $year_min = Carbon::create($time - $detail->max_value, 12, 30)->format('Y-m-d');
                    $data = [
                        'year_min' => $year_min,
                        'year_max' => $year_max,
                    ];
                    $query->orWhereBetween(
                        'survey_profile_inputs.year_of_birth',
                        [
                            $data['year_min'], $data['year_max']
                        ]
                    );
                }
            });
        }
        return $query;
    }
}
