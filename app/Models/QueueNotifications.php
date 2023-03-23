<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QueueNotifications extends Model
{

    protected $fillable = [
        'notify_id',
        'receiving_group',
        'title',
        'content',
        'partner_id',
        'fcm_token',
        'created_at',
        'updated_at',
    ];
    const CLIENT_WEB = 'client_web';
    const PARTNER_APP = 'partner_app';
}
