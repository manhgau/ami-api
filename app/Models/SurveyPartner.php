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

    public static  function getlistSurveyPartner($perPage = 10,  $page = 1, $partner_id, $time_now, $time_end, $is_save = null, $search = null)
    {
        $query = DB::table('survey_partners as a')
            ->join('surveys as b', 'b.id', '=', 'a.survey_id')
            ->select(
                'b.title',
                'a.id as survey_partner_id',
                'b.id as survey_id',
                'b.category_id',
                'b.point',
                'b.state',
                'a.is_save',
                'b.start_time',
                'b.end_time',
                'b.number_of_response_required',
                'b.count_questions',
                'b.view',
            )
            ->where('a.stattus', self::STATUS_ACTIVE)
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.partner_id', $partner_id)
            ->where('b.start_time', '<', $time_now)
            ->where('b.end_time', '>', $time_end)
            ->orderBy('b.created_at', 'desc');
        if ($is_save != null) {
            $query->where('a.is_save', $is_save);
        }
        if ($search != null) {
            $query->where('b.title', 'like', '%' . $search . '%');
        }
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function updateSurveyPartner($data, $survey_partner_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('stattus', self::STATUS_ACTIVE)->where('id', $survey_partner_id)->update($data);
    }

    public static  function getDetailSurveyPartner($survey_partner_id)
    {
        return DB::table('survey_partners as a')
            ->join('surveys as b', 'b.id', '=', 'a.survey_id')
            ->select(
                'a.id',
                'b.title',
                'b.description',
                'b.id as survey_id',
                'b.category_id',
                'b.state',
                'a.is_save',
                'b.point',
                'b.start_time',
                'b.end_time',
                'b.number_of_response_required',
                'b.count_questions',
                'b.view',
            )
            ->where('a.stattus', self::STATUS_ACTIVE)
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.id', $survey_partner_id)
            ->first();
    }
}
