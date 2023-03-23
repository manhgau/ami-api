<?php

namespace App\Helpers;

use App\Models\NotificationsFirebase;
use App\Models\NotificationsFirebaseClients;
use App\Models\UserPackage;
use Carbon\Carbon;

class PushNotificationPackageClient
{
    public static function pushNotificationPackageClient()
    {
        $time = Carbon::now()->addDays(5)->toDate()->format('Y-m-d');
        $list = UserPackage::getAllPackageUser($time);
        if (is_array($list) && count($list) > 0) {
            $template_notification = NotificationsFirebase::getTemplateNotification(NotificationsFirebase::PACKAGE_EXPIRED);
            foreach ($list as $key => $value) {
                $input['title'] = $template_notification['title'];
                $input['notification_id'] =  $template_notification['id'];
                $input['content'] = str_replace("{{package_name}}", $value->name, $template_notification['content']);
                $input['client_id'] =  $value->user_id;
                NotificationsFirebaseClients::create($input);
            }
            return true;
        }
        return false;
    }
}
