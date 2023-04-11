<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotificationsFirebasePartners extends Model
{

    protected $fillable = [
        'title',
        'content',
        'notification_id',
        'partner_id',
        'is_viewed',
        'created_at',
        'updated_at',
    ];

    const VIEW_INACTIVE  = 0;
    const VIEW_ACTIVE  = 1;
    protected $connection = 'mysql-utf8';
    protected $table = 'notifications_firebase_partners';
    protected $primaryKey = 'id';

    public static function getListNotificationPartner($perPage = 100,  $page = 1, $partner_id)
    {
        return DB::table('notifications_firebase_partners as a')
            ->join('notifications_firebases as b', 'b.id', '=', 'a.notification_id')
            ->select(
                'a.id',
                'a.title',
                'a.partner_id',
                'a.is_viewed',
                'a.created_at',
                'a.updated_at',
                'b.id as notification_id',
                'b.notification_type',
                'a.content as description',
            )
            ->where('a.partner_id', $partner_id)
            ->orderBy('a.created_at', 'desc')
            ->paginate($perPage, "*", "page", $page)->toArray();
    }
    public static function getDetailNotificationPartner($partner_id, $notification_partner_id)
    {
        return DB::table('notifications_firebase_partners as a')
            ->join('notifications_firebases as b', 'b.id', '=', 'a.notification_id')
            ->select(
                'a.*',
                'b.id as notification_id',
                'b.notification_type',
            )
            ->where('a.partner_id', $partner_id)
            ->where('a.id', $notification_partner_id)
            ->first();
    }
    public static function countlNotificationPartner($partner_id)
    {
        return self::where('is_viewed', self::VIEW_INACTIVE)->where('partner_id', $partner_id)->count();
    }

    public static  function updateNotificationPartner($data, $id)
    {
        return self::where('is_viewed', self::VIEW_INACTIVE)->where('id', $id)->update($data);
    }
}
