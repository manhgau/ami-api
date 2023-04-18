<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NotificationsFirebaseClients extends Model
{

    protected $fillable = [
        'title',
        'content',
        'notification_id',
        'client_id',
        'is_viewed',
        'created_at',
        'updated_at',
    ];

    const VIEW_INACTIVE  = 0;
    const VIEW_ACTIVE  = 1;

    public static function getListNotificationClient($perPage = 100,  $page = 1, $client_id)
    {
        return DB::table('notifications_firebase_clients as a')
            ->join('notifications_firebases as b', 'b.id', '=', 'a.notification_id')
            ->select(
                'a.id',
                'a.title',
                'a.client_id',
                'a.is_viewed',
                'a.created_at',
                'a.updated_at',
                'b.id as notification_id',
                'b.notification_type',
                'a.content as description',
            )
            ->where('a.client_id', $client_id)
            ->orderBy('a.created_at', 'desc')
            ->paginate($perPage, "*", "page", $page)->toArray();
    }
    public static function getDetailNotificationClient($client_id, $notification_id)
    {
        return DB::table('notifications_firebase_clients as a')
            ->join('notifications_firebases as b', 'b.id', '=', 'a.notification_id')
            ->select(
                'a.*',
                'b.id as notification_id',
                'b.notification_type',
            )
            ->where('a.client_id', $client_id)
            ->where('a.id', $notification_id)
            ->first();
    }
    public static function countlNotificationClient($client_id)
    {
        return self::where('is_viewed', self::VIEW_INACTIVE)->where('client_id', $client_id)->count();
    }

    public static  function updateNotificationClient($data, $id)
    {
        return self::where('is_viewed', self::VIEW_INACTIVE)->where('id', $id)->update($data);
    }

    public static  function deletelNotificationClient($client_id, $notification_id)
    {
        return self::where('client_id', $client_id)->where('id', $notification_id)->delete();
    }
}
