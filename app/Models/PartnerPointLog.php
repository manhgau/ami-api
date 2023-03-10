<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerPointLog extends Model
{
    protected $fillable = [
        'partner_id',
        'partner_input_id',
        'phone',
        'partner_name',
        'type',
        'point',
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
    const PENDING  = 'pending';


    public static  function getPartnerPointLog($partner_id, $survey_id)
    {
        return self::where('partner_id', $partner_id)->where('object_id', $survey_id)->first();
    }

    public static  function updatePartnerPointLog($data, $partner_id, $survey_id)
    {
        return  self::where('partner_id', $partner_id)->where('object_id', $survey_id)->update($data);
    }
}
