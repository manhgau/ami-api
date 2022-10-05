<?php

namespace App\Models;

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
    const STATE_NEW                     = 'new';
    const STATE_DONE                    = 'done';

    const ANYNOMOUS_TRUE                     = 1;
    const ANYNOMOUS_FALSE                    = 0;

    public static  function updateSurveyPartnerInput($data, $id)
    {
        return self::where('id', $id)->update($data);
    }

    public static  function countSurveyInput($survey_id)
    {
        return self::where('survey_id', $survey_id)->where('state', self::STATE_DONE)->count();
    }

    public static  function countSurveyPartnerInput($survey_id, $partner_id)
    {
        return self::where('survey_id', $survey_id)->where('partner_id', $partner_id)->where('state', self::STATE_DONE)->count();
    }
    public static  function getALLSurveyPartnerInput($survey_id,  $question_id, $partner_id)
    {
        return  DB::table('survey_partner_inputs')
            ->join('survey_partner_input_lines', 'survey_partner_input_lines.partner_input_id', '=', 'survey_partner_inputs.id')
            ->where('survey_partner_inputs.partner_id', $partner_id)
            ->where('survey_partner_input_lines.survey_id', $survey_id)
            ->where('survey_partner_input_lines.question_id', $question_id);
    }


    public static  function getlistSurveyPartnerInput($perPage = 10,  $page = 1, $partner_id, $time_now)
    {
        return DB::table('survey_partner_inputs')
            ->join('surveys', 'surveys.id', '=', 'survey_partner_inputs.survey_id')
            ->select(
                'surveys.title',
                'surveys.category_id',
                'surveys.description',
                'surveys.start_time',
                'surveys.end_time',
                'surveys.real_end_time',
                'surveys.count_questions',
                'surveys.number_of_response_required',
                'surveys.number_of_response',
            )
            ->where('survey_partner_inputs.partner_id', $partner_id)
            ->where('surveys.start_time', '<', $time_now)
            ->where('surveys.end_time', '>', $time_now)
            ->orderBy('surveys.created_at', 'desc')
            ->paginate($perPage, "*", "page", $page)->toArray();
    }
}
