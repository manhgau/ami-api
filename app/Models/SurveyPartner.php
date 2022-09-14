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
                'surveys.category_id',
                'surveys.description',
                'surveys.start_time',
                'surveys.end_time',
                'surveys.real_end_time',
                'surveys.count_questions'
            )
            ->where('survey_partners.stattus', self::STATUS_ACTIVE)
            ->where('survey_partners.deleted', self::NOT_DELETED)
            ->where('survey_partners.partner_id', $partner_id)
            ->where('surveys.start_time', '<', $time_now)
            ->where('surveys.end_time', '>', $time_now)
            ->orderBy('surveys.created_at', 'desc')
            ->paginate($perPage, "*", "page", $page)->toArray();
    }
}
