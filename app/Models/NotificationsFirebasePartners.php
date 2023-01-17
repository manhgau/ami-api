<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotificationsFirebasePartners extends Model
{

    protected $fillable = [
        'notification_id',
        'partner_id',
        'is_viewed',
        'created_at',
        'updated_at',
    ];

    const VIEW_INACTIVE  = 0;
    const VIEW_ACTIVE  = 1;

    public static function getListNotificationPartner($perPage = 100,  $page = 1, $partner_id)
    {
        return DB::table('notifications_firebase_partners as a')
            ->join('notifications_firebases as b', 'b.id', '=', 'a.notification_id')
            ->select(
                'a.*',
                'b.id as notification_id',
                'b.title',
                'b.slug',
                'b.description',
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
                'b.title',
                'b.slug',
                'b.description',
                'b.content',
            )
            ->where('a.partner_id', $partner_id)
            ->where('a.id', $notification_partner_id)
            ->first();
    }
    public static function countlNotificationPartner()
    {
        return self::where('is_viewed', self::VIEW_INACTIVE)->count();
    }

    public static  function updateNotificationPartner($data, $id)
    {
        return self::where('is_viewed', self::VIEW_INACTIVE)->where('id', $id)->update($data);
    }
}
