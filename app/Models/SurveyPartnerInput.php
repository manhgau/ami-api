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

    public static  function countSurveyInput($survey_id, $is_anynomous = null)
    {
        $query =  self::where('survey_id', $survey_id)->where('state', self::STATE_DONE);
        if ($is_anynomous != null) {
            $query = $query->where('is_anynomous', $is_anynomous);
        }
        $query = $query->count();
        return $query;
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


    public static  function getlistSurveyPartnerInput($perPage = 10,  $page = 1, $partner_id, $time_now, $time_end)
    {
        return DB::table('survey_partner_inputs')
            ->join('surveys', 'surveys.id', '=', 'survey_partner_inputs.survey_id')
            ->join('survey_partners', 'survey_partners.partner_id', '=', 'survey_partner_inputs.partner_id')
            ->select(
                'survey_partner_inputs.id',
                'surveys.title',
                'survey_partners.is_save',
                'survey_partners.id as survey_partner_id',
                'surveys.id as survey_id',
                'surveys.category_id',
                'surveys.state',
                'surveys.point',
                'surveys.start_time',
                'surveys.end_time',
                'surveys.number_of_response_required',
                'surveys.count_questions',
                'surveys.view',
            )
            ->where('survey_partner_inputs.partner_id', $partner_id)
            ->where('surveys.start_time', '<', $time_now)
            ->where('surveys.end_time', '>', $time_end)
            ->orderBy('surveys.created_at', 'desc')
            ->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailSurveyPartnerInput($survey_partner_input_id, $partner_id)
    {
        return DB::table('survey_partner_inputs')
            ->join('surveys', 'surveys.id', '=', 'survey_partner_inputs.survey_id')
            ->select(
                'survey_partner_inputs.id',
                'surveys.title',
                'surveys.id as survey_id',
                'surveys.category_id',
                'surveys.state',
                'surveys.point',
                'surveys.start_time',
                'surveys.end_time',
                'surveys.number_of_response_required',
                'surveys.count_questions',
                'surveys.view',
            )
            ->where('survey_partner_inputs.id', $survey_partner_input_id)
            ->where('survey_partner_inputs.partner_id', $partner_id)
            ->first();
    }

    public static  function checkPartnerInput($partner_id)
    {
        return self::where('partner_id', $partner_id)->where('state', self::STATE_DONE)->count();
    }
}
