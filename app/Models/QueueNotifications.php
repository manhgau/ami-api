<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueNotifications extends Model
{

    protected $fillable = [
        'notify_id',
        'title',
        'content',
        'partner_id',
        'created_at',
        'updated_at',
    ];
    const CLIENT_WEB = 'client_web';
    const PARTNER_APP = 'partner_app';
}
