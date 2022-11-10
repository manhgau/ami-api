<?php

namespace App\Models;

use App\Helpers\RemoveData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SurveyPartnerInputLine extends Model
{
    protected $fillable = [
        'survey_id',
        'partner_input_id',
        'question_id',
        'question_sequence',
        'skipped',
        'answer_type',
        'value_text_box',
        'value_number',
        'value_date',
        'value_date_start',
        'value_date_end',
        'value_rating_ranking',
        'matrix_row_id',
        'matrix_column_id',
        'answer_score',
        'answer_is_correct',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const SKIP = 1;
    const NOT_SKIP = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getALLSurveyPartnerInputLine($survey_id = 10,  $question_id = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('question_id', $question_id);
    }

    public static  function getALL($perPage = 10,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function sumPartnerAnswer($question_id, $survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('question_id', $question_id)->count();
    }

    public static  function countSurveyPartnerInputLine($partner_input_id, $survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)
            ->where('skipped', 0)->where('partner_input_id', $partner_input_id)->count();
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
