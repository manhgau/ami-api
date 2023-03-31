<?php

namespace App\Models;

use App\Helpers\Firebase;
use Illuminate\Database\Eloquent\Model;

class NotificationsFirebase extends Model
{

    protected $fillable = [
        'title',
        'is_auto',
        'action_auto',
        'description',
        'content',
        'status',
        'push_time',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted',
        'thumbnail'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const AUTO  = 1;
    const NO_AUTO  = 0;
    //Client----------------------------------------------------------------
    const CLEINT_AUTH = 'client_auth';
    const PROJECT_EXPIRED = 'project_expired';
    const PROJECT_NUMBER = 'project_number';
    const PACKAGE_EXPIRED = 'package_expired';
    const PACKAGE_IS_ALMOST_EXPIRED = 'package_is_almost_expired';
    const DATA_STORAGE_EXPIRED = 'data_storage_expires';

    //Partner----------------------------------------------------------------
    const PARTNER_AUTH = 'partner_auth';
    const  PROJECT_COMPLETE_1_1 = 'project_complete_1_1';
    const  PROJECT_NOT_COMPLETE = 'project_not_complete';
    const  PROJECT_COMPLETE_1_N  = 'project_complete_1_n';
    const  PROFILE_COMPLETE  = 'profile_complete';
    const  PROJECT_EXPIRED_HAS_NOT_REACHED_MIN  = 'project_expired_has_not_reached_min';
    const  PROJECT_EXPIRED_HAS_NOT_REACHED_MAX  = 'project_expired_has_not_reached_max';

    public static function getTemplateNotification($key)
    {
        return self::where('action_auto', $key)->where('is_auto', self::AUTO)->where('status', self::STATUS_ACTIVE)->first();
    }
}
