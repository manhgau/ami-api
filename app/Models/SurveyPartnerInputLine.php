<?php

namespace App\Models;

use App\Helpers\RemoveData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;

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
        'value_date',
        'value_date_start',
        'value_date_end',
        'value_star_rating',
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
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getALL($perPage = 10,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function sumPartnerAnswer($question_id, $survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('question_id', $question_id)->count();
    }

    public static  function getSurveyStatisticCheckbox($question_id, $survey_id)
    {
        $result = DB::table('survey_partner_input_lines')
            ->select(DB::raw('count(*) as total , suggested_answer_id'))
            ->where('survey_id', $survey_id)->where('question_id', $question_id)
            ->groupBy('suggested_answer_id')
            ->get();
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

    public static  function getSurveyStatisticRating($question_id, $survey_id)
    {
        $result = DB::table('survey_partner_input_lines')
            ->join('survey_question_answers', 'survey_question_answers.id', '=', 'survey_partner_input_lines.suggested_answer_id')
            ->select(
                'survey_partner_input_lines.value_star_rating',
                'survey_partner_input_lines.answer_type',
                'survey_partner_input_lines.question_sequence',
                'survey_partner_input_lines.suggested_answer_id',
                'survey_question_answers.value as name_answer'
            )
            ->where('survey_partner_input_lines.survey_id', $survey_id)->where('survey_partner_input_lines.question_id', $question_id)
            ->orderBy('survey_partner_input_lines.value_star_rating', 'asc')
            ->get()
            ->groupBy('name_answer');

        foreach ($result as $k => $v) {
            $d = $v->groupBy('value_star_rating');
            $array = [];
            foreach ($d as $key => $item) {
                $array['total'] = count($item);
                $array['value_star_rating'] = $key;
                // $d = [
                //     "1" => [
                //         "total" => 0,
                //         "value_star_rating" => 1
                //     ],
                //     "2" => [
                //         "total" => 0,
                //         "value_star_rating" => 2
                //     ],
                //     "3" => [
                //         "total" => 0,
                //         "value_star_rating" => 3
                //     ],
                //     "4" => [
                //         "total" => 0,
                //         "value_star_rating" => 4
                //     ],
                //     "5" => [
                //         "total" => 0,
                //         "value_star_rating" => 5
                //     ],
                // ];
                $d[$key] = $array;
            }
            $result[$k] = $d;
        }
        return $result;
    }


    public static  function getSurveyStatisticTextOrDate($perPage, $page, $question_id, $survey_id, $question_type)
    {
        $result = DB::table('survey_partner_input_lines')
            ->select('question_sequence', 'answer_type', 'value_text_box', 'value_date', 'value_date_start', 'value_date_end')
            ->where('survey_id', $survey_id)->where('question_id', $question_id)
            ->paginate($perPage, "*", "page", $page)->toArray();
        $result =    RemoveData::removeUnusedData($result);
        $data = [];
        foreach ($result['data'] as $key => $value) {
            switch ($question_type) {
                case QuestionType::DATETIME_DATE:
                    $data[$key] = $value->value_date;
                    break;
                case QuestionType::DATETIME_DATE_RANGE:
                    $data[$key] = $value->value_date_start . '-' . $value->value_date_end;
                    break;
                case QuestionType::QUESTION_ENDED_SHORT_TEXT:
                case QuestionType::QUESTION_ENDED_LONG_TEXT:
                    $data[$key] = $value->value_text_box;
                    break;
                default:
                    return false;
                    break;
            }
        }
        $result['data'] = $data;
        return $result;
    }

    public static  function getSurveyStatisticMatrix($question_id, $survey_id)
    {
        $result = DB::table('survey_partner_input_lines')
            ->join('survey_question_answers', 'survey_question_answers.id', '=', 'survey_partner_input_lines.matrix_column_id')
            ->select(
                'survey_partner_input_lines.matrix_row_id',
                'survey_partner_input_lines.matrix_column_id',
                'survey_question_answers.value as name_answer_column',
            )
            ->where('survey_partner_input_lines.survey_id', $survey_id)->where('survey_partner_input_lines.question_id', $question_id)
            ->get()
            ->groupBy('name_answer_column');
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
