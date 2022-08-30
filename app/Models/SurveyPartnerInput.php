<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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


}
