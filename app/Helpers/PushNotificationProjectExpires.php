<?php

namespace App\Helpers;

use App\Models\NotificationsFirebase;
use App\Models\NotificationsFirebaseClients;
use App\Models\Survey;
use App\Models\UserPackage;
use Carbon\Carbon;

class PushNotificationProjectExpires
{
    public static function pushNotificationProjectExpires()
    {
        $list_survey_expired = Survey::listSurveyTimeUp();
        if (is_array($list_survey_expired) && count($list_survey_expired) > 0) {
            $template_notification = NotificationsFirebase::getTemplateNotification(NotificationsFirebase::PROJECT_EXPIRED);
            if ($template_notification) {
                foreach ($list_survey_expired as $key => $value) {
                    if ($template_notification) {
                        $input['content'] = str_replace("{{project_name}}", $value['title'], $template_notification['content']);
                        $input['title'] = $template_notification['title'];
                        $input['client_id'] =  $value['user_id'];
                        $input['notification_id'] =  $template_notification['id'];
                        NotificationsFirebaseClients::create($input);
                    }
                }
                return true;
            }
        }
        return false;
    }
}
