<?php

namespace App\Helpers;

use App\Models\NotificationsFirebase;
use Carbon\Carbon;

class Firebase
{
    const ACCESS_KEY = "AAAAHfytLnM:APA91bHxV0hlYj8TEZC7YvwTHA6E9i40WWRYhpx7EqPHaEKrBgynKHuo6k9Q9ZRt4cel2NTYFPIUWlgsk9qN_lGrX7GazWC5R3eUt8OqIneDwkrboOp4zsRgJ5tpsl3GPWaWBcEImxwF";


    public static function notify()
    {
        //Start push
        $notify = NotificationsFirebase::query()
            ->where('push_time', '<=', Carbon::now())
            ->where('is_push', 0)
            ->first();
        if ($notify) {
            $result = $notify->sendNotifyTopicFCM();
            if ($result) {
                $notify->is_push = 1;
                $notify->save();
                return true;
            }
        }
        //end push
        return false;
    }
}
