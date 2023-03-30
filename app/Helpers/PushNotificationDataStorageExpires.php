<?php

namespace App\Helpers;

use App\Models\NotificationsFirebase;
use App\Models\NotificationsFirebaseClients;
use App\Models\Survey;
use App\Models\UserPackage;
use Carbon\Carbon;

class PushNotificationDataStorageExpires
{
    public static function pushNotificationDataStorageExpires()
    {
        $list_project = Survey::getAllSurvey();
        if (is_array($list_project) && count($list_project) > 0) {
            $template_notification = NotificationsFirebase::getTemplateNotification(NotificationsFirebase::DATA_STORAGE_EXPIRED);
            if ($template_notification) {
                foreach ($list_project as $key => $value) {
                    $user_package = UserPackage::getPackageUser($value['user_id'], Carbon::now());
                    if ($user_package['data_storage'] > 0) {
                        $time = Carbon::now()->addDays(- ($user_package['data_storage'] - 2))->toDate()->format('Y-m-d');
                        $start_time = date_format(date_create($value['start_time']), 'Y-m-d');
                        if (strtotime($time) ==  strtotime($start_time)) {
                            $input['title'] = $template_notification['title'];
                            $input['notification_id'] =  $template_notification['id'];
                            $input['content'] = str_replace("{{project_name}}", $value['title'], $template_notification['content']);
                            $input['client_id'] =  $value['user_id'];
                            NotificationsFirebaseClients::create($input);
                        };
                    }
                }
                return true;
            }
        }
        return false;
    }
}
