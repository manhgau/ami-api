<?php

namespace App\Models;

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
        'deadline',
        'state',
        'phone',
        'fullname',
        'is_anynomous',
        'a_partner_id',
        'ip',
        'os',
        'browser',
        'user_agent',
        'created_at',
        'updated_at',
    ];
    const NEW = 'new';
    const DONE = 'done';
    const SKIP = 1;
    const NOT_SKIP = 0;

    const CLOSED  = 'closed';
    const ON_PROGRESS  = 'on_progress';
    const NOT_PROGRESS  = 'not_progress';

    const ANYNOMOUS_TRUE                     = 1;
    const ANYNOMOUS_FALSE                    = 0;

    public static  function updateSurveyPartnerInput($data, $id)
    {
        return self::where('id', $id)->update($data);
    }

    public static  function countSurveyInput($survey_id, $is_anynomous = null)
    {
        $query =  self::where('survey_id', $survey_id)->where('state', self::DONE);
        if ($is_anynomous != null) {
            $query = $query->where('is_anynomous', $is_anynomous);
        }
        $query = $query->count();
        return $query;
    }

    public static  function countSurveyPartnerInput($survey_id, $partner_id)
    {
        return self::where('survey_id', $survey_id)->where('partner_id', $partner_id)->where('state', self::DONE)->count();
    }
    public static  function getALLSurveyPartnerInput($survey_id,  $question_id, $partner_id)
    {
        return  DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->where('survey_partner_inputs.partner_id', $partner_id)
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.question_id', $question_id);
    }


    public static  function getlistSurveyPartnerInput($perPage = 10,  $page = 1, $partner_id, $time_now, $time_end, $search = null, $status = null)
    {
        $query =  DB::table('survey_partner_inputs as a')
            ->join('surveys as b', 'b.id', '=', 'a.survey_id')
            ->join('survey_partners as c', 'c.survey_id', '=', 'b.id')
            ->select(
                'a.id',
                'b.title',
                'c.is_save',
                'c.id as survey_partner_id',
                'b.id as survey_id',
                'b.category_id',
                'b.state',
                'b.point',
                'b.start_time',
                'b.end_time',
                'b.number_of_response_required',
                'b.count_questions',
                'b.view',
            )
            ->where('a.partner_id', $partner_id)
            ->where('b.start_time', '<', $time_now)
            ->where('b.end_time', '>', $time_end)
            ->where('c.partner_id', $partner_id)
            ->orderBy('b.created_at', 'desc')
            ->distinct();
        if ($search != null) {
            $query->where('b.title', 'like', '%' . $search . '%');
        }
        if ($status == self::CLOSED) {
            $query->where('b.end_time', '<', Carbon::now())
                ->whereColumn('b.attempts_limit_min', '>', 'c.number_of_response');
        }
        if ($status == self::ON_PROGRESS) {
            $query->where(function ($query) {
                $query->orwhere(function ($query) {
                    $query->where('b.state', Survey::STATUS_ON_PROGRESS)
                        ->whereColumn('b.attempts_limit_max', 'c.number_of_response');
                });
                $query->orwhere(function ($query) {
                    $query->where('b.end_time', '<', Carbon::now())
                        ->whereColumn('b.attempts_limit_min', '<=', 'c.number_of_response');
                });
            });
        }
        if ($status == self::NOT_PROGRESS) {
            $query->where('b.state', Survey::STATUS_ON_PROGRESS)
                ->whereColumn('b.attempts_limit_max', '>', 'c.number_of_response');
        }
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailSurveyPartnerInput($survey_partner_input_id, $partner_id)
    {
        return DB::table('survey_partner_inputs as a')
            ->join('surveys as b', 'b.id', '=', 'a.survey_id')
            ->select(
                'a.id',
                'b.title',
                'b.id as survey_id',
                'b.category_id',
                'b.state',
                'b.point',
                'b.start_time',
                'b.end_time',
                'b.number_of_response_required',
                'b.count_questions',
                'b.view',
            )
            ->where('a.id', $survey_partner_input_id)
            ->where('a.partner_id', $partner_id)
            ->first();
    }

    public static  function checkPartnerInput($partner_id, $survey_id)
    {
        return self::where('partner_id', $partner_id)->where('survey_id', $survey_id)->where('state', self::DONE)->count();
    }

    public static  function getDiagramSurvey(
        $survey_id,
        $start_time = null,
        $end_time = null,
        $academic_level_ids = null,
        $province_codes = null,
        $gender = null,
        $year_of_birth = null,
        $job_type_ids = null,
        $marital_status_ids = null,
        $family_peoples = null,
        $has_children = null,
        $is_key_shopper = null
    ) {
        $result = DB::table('survey_partner_inputs')
            ->join('survey_profile_inputs', 'survey_profile_inputs.partner_id', '=', 'survey_partner_inputs.partner_id')
            ->join('provinces', 'provinces.code', '=', 'survey_profile_inputs.province_code')
            ->join('genders', 'genders.id', '=', 'survey_profile_inputs.gender')
            ->join('academic_levels', 'academic_levels.id', '=', 'survey_profile_inputs.academic_level_id')
            ->join('personal_income_levels', 'personal_income_levels.id', '=', 'survey_profile_inputs.personal_income_level_id')
            ->select(
                'provinces.name as province_name',
                'genders.name as gender_name',
                'academic_levels.name as academic_level_name',
                'personal_income_levels.name as personal_income_level_name'
            )
            ->where('survey_partner_inputs.survey_id', $survey_id)
            ->where('survey_partner_inputs.is_anynomous', self::ANYNOMOUS_FALSE);
        if ($start_time != null && $end_time != null) {
            $result->whereBetween('survey_partner_inputs.created_at', [$start_time, $end_time]);
        };
        if ($gender != null) {
            $result->whereIn('survey_profile_inputs.gender', $gender);
        };
        if ($year_of_birth != null) {
            $result->whereIn('survey_profile_inputs.year_of_birth', $year_of_birth);
        };
        if ($academic_level_ids != null) {
            $result->whereIn('survey_profile_inputs.academic_level_id', $academic_level_ids);
        };
        if ($province_codes != null) {
            $result->whereIn('survey_profile_inputs.province_code', $province_codes);
        };
        if ($job_type_ids != null) {
            $result->whereIn('survey_profile_inputs.job_type_id', $job_type_ids);
        };
        if ($marital_status_ids != null) {
            $result->whereIn('survey_profile_inputs.marital_status_id', $marital_status_ids);
        };
        if ($family_peoples != null) {
            $result->whereIn('survey_profile_inputs.family_people', $family_peoples);
        };
        if ($marital_status_ids != null) {
            $result->whereIn('survey_profile_inputs.marital_status_id', $marital_status_ids);
        };
        if ($is_key_shopper != null) {
            $result->whereIn('survey_profile_inputs.is_key_shopper', $is_key_shopper);
        };
        if ($has_children != null) {
            $result->whereIn('survey_profile_inputs.has_children', $has_children);
        };
        $result = $result->get();
        return $result;
    }

    public static  function getDiagramYearOfBirth(
        $survey_id,
        $year_min,
        $year_max,
        $start_time = null,
        $end_time = null,
        $academic_level_ids = null,
        $province_codes = null,
        $gender = null,
        $year_of_birth = null,
        $job_type_ids = null,
        $marital_status_ids = null,
        $family_peoples = null,
        $has_children = null,
        $is_key_shopper = null
    ) {
        $result = DB::table('survey_partner_inputs')
            ->join('survey_profile_inputs', 'survey_profile_inputs.partner_id', '=', 'survey_partner_inputs.partner_id')
            ->where('survey_partner_inputs.survey_id', $survey_id)
            ->where('survey_partner_inputs.is_anynomous', self::ANYNOMOUS_FALSE)
            ->whereYear('year_of_birth', '>=', $year_min)
            ->whereYear('year_of_birth', '<=', $year_max);
        if ($start_time != null && $end_time != null) {
            $result->whereBetween('survey_partner_inputs.created_at', [$start_time, $end_time]);
        }
        if ($gender != null) {
            $result->whereIn('survey_profile_inputs.gender', $gender);
        }
        if ($year_of_birth != null) {
            $result->whereIn('survey_profile_inputs.year_of_birth', $year_of_birth);
        }
        if ($academic_level_ids != null) {
            $result->whereIn('survey_profile_inputs.academic_level_id', $academic_level_ids);
        }
        if ($province_codes != null) {
            $result->whereIn('survey_profile_inputs.province_code', $province_codes);
        }
        if ($job_type_ids != null) {
            $result->whereIn('survey_profile_inputs.job_type_id', $job_type_ids);
        }
        if ($marital_status_ids != null) {
            $result->whereIn('survey_profile_inputs.marital_status_id', $marital_status_ids);
        }
        if ($family_peoples != null) {
            $result->whereIn('survey_profile_inputs.family_people', $family_peoples);
        }
        if ($marital_status_ids != null) {
            $result->whereIn('survey_profile_inputs.marital_status_id', $marital_status_ids);
        }
        if ($is_key_shopper != null) {
            $result->whereIn('survey_profile_inputs.is_key_shopper', $is_key_shopper);
        }
        if ($has_children != null) {
            $result->whereIn('survey_profile_inputs.has_children', $has_children);
        }
        return $result->get();
    }

    public static  function getStatisticSurvey(
        $survey_id,
        $is_anynomous = null,
        $start_time = null,
        $end_time = null,
        $academic_level_ids = null,
        $province_codes = null,
        $gender = null,
        $year_of_birth = null,
        $job_type_ids = null,
        $marital_status_ids = null,
        $family_peoples = null,
        $has_children = null,
        $is_key_shopper = null
    ) {
        $result = DB::table('survey_partner_inputs')
            ->join('surveys', 'surveys.id', '=', 'survey_partner_inputs.survey_id')
            ->join('survey_profile_inputs', 'survey_profile_inputs.partner_id', '=', 'survey_partner_inputs.partner_id')
            ->select(
                'survey_partner_inputs.start_datetime',
                'survey_partner_inputs.end_datetime',
                'survey_partner_inputs.skip',
                'survey_partner_inputs.state',
            )
            ->where('survey_partner_inputs.survey_id', $survey_id);
        if ($is_anynomous != null) {
            $result->where('survey_partner_inputs.is_anynomous', $is_anynomous);
        }
        if ($start_time != null && $end_time != null) {
            $result->whereBetween('survey_partner_inputs.created_at', [$start_time, $end_time]);
        }
        if ($gender != null) {
            $result->whereIn('survey_profile_inputs.gender', $gender);
        }
        if ($year_of_birth != null) {
            $result->whereIn('survey_profile_inputs.year_of_birth', $year_of_birth);
        }
        if ($academic_level_ids != null) {
            $result->whereIn('survey_profile_inputs.academic_level_id', $academic_level_ids);
        }
        if ($province_codes != null) {
            $result->whereIn('survey_profile_inputs.province_code', $province_codes);
        }
        if ($job_type_ids != null) {
            $result->whereIn('survey_profile_inputs.job_type_id', $job_type_ids);
        }
        if ($marital_status_ids != null) {
            $result->whereIn('survey_profile_inputs.marital_status_id', $marital_status_ids);
        }
        if ($family_peoples != null) {
            $result->whereIn('survey_profile_inputs.family_people', $family_peoples);
        }
        if ($marital_status_ids != null) {
            $result->whereIn('survey_profile_inputs.marital_status_id', $marital_status_ids);
        }
        if ($is_key_shopper != null) {
            $result->whereIn('survey_profile_inputs.is_key_shopper', $is_key_shopper);
        }
        if ($has_children != null) {
            $result->whereIn('survey_profile_inputs.has_children', $has_children);
        }
        return $result;
    }

    public static  function getStatisticQuestionsSurvey(
        $survey_id,
        $question_id,
        $is_anynomous = null,
        $start_time = null,
        $end_time = null,
        $academic_level_ids = null,
        $province_codes = null,
        $gender = null,
        $year_of_birth = null,
        $job_type_ids = null,
        $marital_status_ids = null,
        $family_peoples = null,
        $has_children = null,
        $is_key_shopper = null
    ) {
        $result = DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->join('survey_profile_inputs', 'survey_profile_inputs.partner_id', '=', 'survey_partner_inputs.partner_id')
            ->select(
                'survey_partner_inputs.partner_id',
                'survey_partner_input_lines.skipped',
            )
            ->where('survey_partner_input_lines.question_id', $question_id)
            ->where('survey_partner_inputs.survey_id', $survey_id);
        if ($is_anynomous != null) {
            $result->where('survey_partner_inputs.is_anynomous', $is_anynomous);
        }
        if ($start_time != null && $end_time != null) {
            $result->whereBetween('survey_partner_inputs.created_at', [$start_time, $end_time]);
        }
        if ($gender != null) {
            $result->whereIn('survey_profile_inputs.gender', $gender);
        }
        if ($year_of_birth != null) {
            $result->whereIn('survey_profile_inputs.year_of_birth', $year_of_birth);
        }
        if ($academic_level_ids != null) {
            $result->whereIn('survey_profile_inputs.academic_level_id', $academic_level_ids);
        }
        if ($province_codes != null) {
            $result->whereIn('survey_profile_inputs.province_code', $province_codes);
        }
        if ($job_type_ids != null) {
            $result->whereIn('survey_profile_inputs.job_type_id', $job_type_ids);
        }
        if ($marital_status_ids != null) {
            $result->whereIn('survey_profile_inputs.marital_status_id', $marital_status_ids);
        }
        if ($family_peoples != null) {
            $result->whereIn('survey_profile_inputs.family_people', $family_peoples);
        }
        if ($marital_status_ids != null) {
            $result->whereIn('survey_profile_inputs.marital_status_id', $marital_status_ids);
        }
        if ($is_key_shopper != null) {
            $result->whereIn('survey_profile_inputs.is_key_shopper', $is_key_shopper);
        }
        if ($has_children != null) {
            $result->whereIn('survey_profile_inputs.has_children', $has_children);
        }
        return $result;
    }

    public static  function getSurveyStatisticCheckbox($question_id, $survey_id, $is_anynomous = null)
    {
        $result = DB::table('survey_partner_input_lines')
            ->join('survey_partner_inputs', 'survey_partner_inputs.id', '=', 'survey_partner_input_lines.partner_input_id')
            ->select(DB::raw('count(*) as total , survey_partner_input_lines.suggested_answer_id'))
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.question_id', $question_id);
        if ($is_anynomous != null) {
            $result->where('survey_partner_inputs.is_anynomous', $is_anynomous);
        };
        $result = $result->groupBy('suggested_answer_id')->get();
        $data_results = array();
        foreach ($result as $key => $value) {
            $name_answer = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($value->suggested_answer_id);
            $input['suggested_answer_id'] = $value->suggested_answer_id;
            $input['name_answer'] = $name_answer->value;
            $input['number_partner_answer'] = $value->total;
            $data_results[$key] = $input;
        }
        return $data_results;
    }

    public static  function getSurveyStatisticRating($question_id, $survey_id, $is_anynomous = null)
    {
        $result = DB::table('survey_partner_input_lines')
            ->join('survey_question_answers', 'survey_question_answers.id', '=', 'survey_partner_input_lines.suggested_answer_id')
            ->join('survey_partner_inputs', 'survey_partner_inputs.id', '=', 'survey_partner_input_lines.partner_input_id')
            ->select(
                'survey_partner_input_lines.value_rating_ranking',
                'survey_partner_input_lines.answer_type',
                'survey_partner_input_lines.question_sequence',
                'survey_partner_input_lines.suggested_answer_id',
                'survey_question_answers.value as name_answer'
            )
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.question_id', $question_id);
        if ($is_anynomous != null) {
            $result->where('survey_partner_inputs.is_anynomous', $is_anynomous);
        };
        $result = $result->orderBy('survey_partner_input_lines.value_rating_ranking', 'asc')
            ->get()
            ->groupBy('name_answer');

        foreach ($result as $k => $v) {
            $d = $v->groupBy('value_rating_ranking');
            $array = [];
            foreach ($d as $key => $item) {
                $array['total'] = count($item);
                $array['value_rating_ranking'] = $key;
                $d[$key] = $array;
            }
            $result[$k] = $d;
        }
        return $result;
    }


    public static  function getSurveyStatisticTextOrDate($perPage, $page, $question_id, $survey_id, $question_type, $is_anynomous = null)
    {
        $result = DB::table('survey_partner_input_lines')
            ->join('survey_partner_inputs', 'survey_partner_inputs.id', '=', 'survey_partner_input_lines.partner_input_id')
            ->select(
                'survey_partner_input_lines.question_sequence',
                'survey_partner_input_lines.answer_type',
                'survey_partner_input_lines.value_text_box',
                'survey_partner_input_lines.value_date',
                'survey_partner_input_lines.value_date_start',
                'survey_partner_input_lines.value_date_end'
            )
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.question_id', $question_id);
        if ($is_anynomous != null) {
            $result->where('survey_partner_inputs.is_anynomous', $is_anynomous);
        };
        $result = $result->paginate($perPage, "*", "page", $page)->toArray();
        $result =    RemoveData::removeUnusedData($result);
        $data = [];
        foreach ($result['data'] as $key => $value) {
            switch ($question_type) {
                case QuestionType::DATETIME_DATE:
                    $input['value'] = $value->value_date;
                    $data[$key] = $input;
                    break;
                case QuestionType::DATETIME_DATE_RANGE:
                    $input['value'] = $value->value_date_start . '-' . $value->value_date_end;
                    $data[$key] = $input;
                    break;
                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                    $input['value'] = $value->value_text_box;
                    $data[$key] = $input;
                    break;
                default:
                    return false;
                    break;
            }
        }
        $result['data'] = $data;
        return $result;
    }

    public static  function getSurveyStatisticMatrix($question_id, $survey_id, $is_anynomous = null)
    {
        $result = DB::table('survey_partner_input_lines')
            ->join('survey_question_answers', 'survey_question_answers.id', '=', 'survey_partner_input_lines.matrix_column_id')
            ->join('survey_partner_inputs', 'survey_partner_inputs.id', '=', 'survey_partner_input_lines.partner_input_id')
            ->select(
                'survey_partner_input_lines.matrix_row_id',
                'survey_partner_input_lines.matrix_column_id',
                'survey_question_answers.value as name_answer_column',
            )
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.question_id', $question_id);
        if ($is_anynomous != null) {
            $result->where('survey_partner_inputs.is_anynomous', $is_anynomous);
        };
        $result = $result->get()->groupBy('name_answer_column');
        foreach ($result as $key => $value) {
            foreach ($value as $k => $item) {
                $input = json_encode($item);
                $name_answer_row = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($item->matrix_row_id)->value;
                $input = json_decode($input, true);
                $input['name_answer_row'] = $name_answer_row;
                $value[$k] = $input;
            }
            $group = $value->groupBy('name_answer_row');
            $array = [];
            foreach ($group as $h => $cat) {
                $array['total'] = count($cat);
                $array['name_answer_row'] = $h;
                $group[$h] = $array;
            }
            $result[$key] = $group;
        }
        return $result;
    }
}
