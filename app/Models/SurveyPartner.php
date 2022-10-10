<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SurveyPartner extends Model
{

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getlistSurveyPartner($perPage = 10,  $page = 1, $partner_id, $time_now)
    {
        return DB::table('survey_partners')
            ->join('surveys', 'surveys.id', '=', 'survey_partners.survey_id')
            ->select(
                'surveys.title',
                'survey_partners.id',
                'surveys.id as survey_id',
                'surveys.category_id',
                'surveys.description',
                'surveys.start_time',
                'surveys.end_time',
                'surveys.real_end_time',
                'surveys.count_questions',
                'surveys.view',
            )
            ->where('survey_partners.stattus', self::STATUS_ACTIVE)
            ->where('survey_partners.deleted', self::NOT_DELETED)
            ->where('survey_partners.partner_id', $partner_id)
            ->where('surveys.start_time', '<', $time_now)
            ->where('surveys.state', '<', $time_now)
            ->where('surveys.end_time', Survey::STATUS_ON_PROGRESS)
            ->orderBy('surveys.created_at', 'desc')
            ->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function getDetailSurveyPartner($survey_partner_id, $time_now)
    {
        return DB::table('survey_partners')
            ->join('surveys', 'surveys.id', '=', 'survey_partners.survey_id')
            ->select(
                'survey_partners.id',
                'surveys.title',
                'surveys.id as survey_id',
                'surveys.category_id',
                'surveys.description',
                'surveys.start_time',
                'surveys.end_time',
                'surveys.real_end_time',
                'surveys.count_questions',
                'surveys.view',
            )
            ->where('survey_partners.stattus', self::STATUS_ACTIVE)
            ->where('survey_partners.deleted', self::NOT_DELETED)
            ->where('surveys.end_time', Survey::STATUS_ON_PROGRESS)
            ->where('survey_partners.id', $survey_partner_id)
            ->where('surveys.start_time', '<', $time_now)
            ->where('surveys.state', '<', $time_now)
            ->first();
    }
}
