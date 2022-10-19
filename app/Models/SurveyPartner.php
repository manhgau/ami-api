<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SurveyPartner extends Model
{
    protected $fillable = ['is_save'];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const SAVE = 1;
    const NO_SAVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getlistSurveyPartner($perPage = 10,  $page = 1, $partner_id, $time_now, $time_end, $is_save = null)
    {
        $query = DB::table('survey_partners')
            ->join('surveys', 'surveys.id', '=', 'survey_partners.survey_id')
            ->select(
                'surveys.title',
                'survey_partners.id as survey_partner_id',
                'surveys.id as survey_id',
                'surveys.category_id',
                'surveys.point',
                'surveys.state',
                'survey_partners.is_save',
                'surveys.start_time',
                'surveys.end_time',
                'surveys.number_of_response_required',
                'surveys.count_questions',
                'surveys.view',
            )
            ->where('survey_partners.stattus', self::STATUS_ACTIVE)
            ->where('survey_partners.deleted', self::NOT_DELETED)
            ->where('survey_partners.partner_id', $partner_id)
            ->where('surveys.start_time', '<', $time_now)
            ->where('surveys.end_time', '>', $time_end)
            ->orderBy('surveys.created_at', 'desc');
        if ($is_save != null) {
            $query->where('survey_partners.is_save', $is_save);
        }
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function updateSurveyPartner($data, $survey_partner_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('stattus', self::STATUS_ACTIVE)->where('id', $survey_partner_id)->update($data);
    }

    public static  function getDetailSurveyPartner($survey_partner_id)
    {
        return DB::table('survey_partners')
            ->join('surveys', 'surveys.id', '=', 'survey_partners.survey_id')
            ->select(
                'survey_partners.id',
                'surveys.title',
                'surveys.description',
                'surveys.id as survey_id',
                'surveys.category_id',
                'surveys.state',
                'survey_partners.is_save',
                'surveys.point',
                'surveys.start_time',
                'surveys.end_time',
                'surveys.number_of_response_required',
                'surveys.count_questions',
                'surveys.view',
            )
            ->where('survey_partners.stattus', self::STATUS_ACTIVE)
            ->where('survey_partners.deleted', self::NOT_DELETED)
            ->where('survey_partners.id', $survey_partner_id)
            ->first();
    }
}
