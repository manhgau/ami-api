<?php

namespace App\Helpers;

use App\Models\NotificationsFirebase;
use Carbon\Carbon;

class Firebase{
    const ACCESS_KEY = "AAAAHfytLnM:APA91bHxV0hlYj8TEZC7YvwTHA6E9i40WWRYhpx7EqPHaEKrBgynKHuo6k9Q9ZRt4cel2NTYFPIUWlgsk9qN_lGrX7GazWC5R3eUt8OqIneDwkrboOp4zsRgJ5tpsl3GPWaWBcEImxwF";


    public static function notify(){
        //Start push
        $notify = NotificationsFirebase::query()
            ->where('push_time', '<=', Carbon::now())
            ->where('is_push', 0)
            ->first();
        if($notify) {
            $notify->sendNotifyTopicFCM();
            $notify->response = 'OK';
            $notify->is_push = 1;
            $notify->save();
            return true;
        }
        //end push
        return false;
    }

    public static function testNotification($registrationIds, $title = '', $content = '', $content_type = 0, $content_id = 0){
        $data = [
            'title'             => $title,
            'content'           => $content,
            'content_id'        => $content_id,
            'content_type'      => $content_type,
        ];
        $fields = array(
            'registration_ids' => $registrationIds,
            'data' => $data,
            'notification' => [
                'title' => $title,
                'body' => $content
            ]
        );
        $headers = array(
            'Authorization: key=' . self::ACCESS_KEY,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch );
        curl_close( $ch );
        echo '<pre>';
        print_r($fields);
        print_r($result);
        echo '</pre>';
        return $result;
    }
}