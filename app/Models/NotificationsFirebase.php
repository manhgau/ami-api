<?php

namespace App\Models;

use App\Helpers\Firebase;
use Illuminate\Database\Eloquent\Model;

class NotificationsFirebase extends Model
{

    protected $fillable = [
        'title',
        'slug',
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

    public function sendNotifyTopicFCM(){
        $data = [
            'id'                => $this->id,
            'title'             => $this->title,
            'slug'              => $this->slug,
            'content'           => $this->content,
            'description'       => $this->description,
            'thumbnail'         => $this->thumbnail,
        ];
        $tos = MappingUidFcmToken::query()
        ->where('status_fcm', 0)
        ->orderBy('created_at')
        ->get();
        foreach ($tos as $to) {
            $push = [
                'to' => $to->fcm_token,
                'data' => $data,
                'notification' => [
                    'title'             => $this->title,
                    'body'              => $this->content,
                    'thumbnail'         => $this->thumbnail,
                ]
            ];
            dd($push);
            $this->sendFCM($push);
        }
        return true;
    }

    public function sendFCM($data){
        $url = 'https://fcm.googleapis.com/fcm/send';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: key=' . Firebase::ACCESS_KEY,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $data = curl_exec($ch);
        $res = json_decode($data);
        return $res;
    }


}
