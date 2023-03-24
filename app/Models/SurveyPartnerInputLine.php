<?php

namespace App\Models;

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

    public static  function deletePartnerInputLine($survey_id, $partner_input_id, $question_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)
            ->where('question_id', $question_id)->where('partner_input_id', $partner_input_id)->delete();
    }

    public static  function listInputLine($partner_input_id, $question_id,  $matrix_row_id = null)
    {
        $query =  DB::table('survey_partner_input_lines as a')
            ->select(
                'a.*',
            )
            ->where('a.partner_input_id', $partner_input_id)
            ->where('a.question_id', $question_id);
        if ($matrix_row_id != null) {
            $query->where('a.matrix_row_id', $matrix_row_id);
        }
        return $query->orderBy('a.id', 'asc')->get();
    }
}
