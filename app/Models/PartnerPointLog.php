<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PartnerPointLog extends Model
{
    protected $fillable = [
        'partner_id',
        'partner_input_id',
        'phone',
        'partner_name',
        'type',
        'point',
        'kpi_point',
        'created_at',
        'updated_at',
        'created_by',
        'action',
        'object_type',
        'object_id',
        'note',
        'status',
    ];
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const TRU  = -1;
    const CONG  = 1;

    const ACTION_FINISHED_ANSWER_SURVEY = 'finished_answer_survey';
    const ACTION_REDEEM_REWARD  = 'redeem_reward';
    const TYPE_OBJ_SURVEY  = 'survey';
    const SUCCESS = 'success';
    const FAIL = 'fail';
    const UNQUALIFIED =  'unqualified';
    const PENDING =  'pending';

    public static  function getPointPendingOfPartner($partner_id)
    {
        $query = self::where('partner_id', $partner_id)
            ->where('type', self::CONG)
            ->where(function ($query) {
                $query->orWhere('status', PartnerPointLog::PENDING)
                    ->orWhere('status', PartnerPointLog::UNQUALIFIED);
            });
        return $query->get()->sum('point');
    }

    public static  function getPointFailOfPartner($partner_id)
    {
        return self::where('partner_id', $partner_id)
            ->where('type', self::CONG)
            ->where('status', PartnerPointLog::FAIL)
            ->get()->sum('point');
    }

    public static  function updatePartnerPointLog($data, $partner_id, $survey_id)
    {
        return  self::where('partner_id', $partner_id)->where('object_id', $survey_id)->update($data);
    }

    public static  function getListHistoryPointLog($perPage = 10,  $page = 1, $status)
    {
        $query =  DB::table('partner_point_logs as a')
            ->leftJoin('surveys as b', 'b.id', '=', 'a.object_id')
            ->select(
                'a.*',
                'b.title',
            )
            ->where('a.status', $status)
            ->orderBy('a.created_at', 'desc');
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }
}
