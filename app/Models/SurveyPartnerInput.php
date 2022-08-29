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
        'created_at',
        'updated_at',
    ];

    const STATE_NEW                     = 'new';
    const STATE_DONE                    = 'done';




}
