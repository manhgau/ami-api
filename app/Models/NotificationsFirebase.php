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

    public function sendNotifyTopicFCM()
    {
        $all_settings = AppSetting::getAllSetting();
        //setting
        $image_domain  = AppSetting::getByKey(AppSetting::IMAGE_DOMAIN, $all_settings);
        $data = [
            'id'                => $this->id,
            'title'             => $this->title,
            'slug'              => $this->slug,
            'content'           => $this->content,
            'description'       => $this->description,
            'thumbnail'         => $image_domain . $this->thumbnail,
        ];
        $tos = MappingUidFcmToken::query()
            ->where('status_fcm', 0)
            ->orderBy('created_at')
            ->get();
        foreach ($tos as $to) {
            $push = [
                'to' => $to->fcm_token,
                'data' => $data,
            ];
            //dd($push);
            $this->sendFCM($push);
        }
        return true;
    }

    public function sendFCM($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Authorization: key=' . Firebase::ACCESS_KEY,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
