<?php

namespace App\Helpers;

use App\Models\MappingUidFcmToken;
use App\Models\NotificationsFirebase;
use App\Models\QueueNotifications;
use App\Models\Survey;
use App\Models\SurveyPartner;
use App\Models\SurveyPartnerInput;
use App\Models\UserPackage;
use Carbon\Carbon;

class CheckProjectIsAlsmostExpires
{
    public static function checkProjectIsAlmostExpires()
    {
        $time = Carbon::now()->addDays(2)->toDate()->format('Y-m-d');
        $list_project = SurveyPartner::getAllSurveyIsAlmostExpires($time);
        if (is_array($list_project) && count($list_project) > 0) {
            foreach ($list_project as $key => $value) {
                if ($value->is_answer_single == Survey::ANSWER_MULTIPLE) {
                    $number_of_response = SurveyPartnerInput::countSurveyPartnerInput($value->survey_id, $value->partner_id);
                    if ($number_of_response < $value->attempts_limit_min) {
                        $fcm_token = MappingUidFcmToken::getMappingUidFcmTokenByPartnerId($value->partner_id)->fcm_token ?? null;
                        $input['fcm_token'] = $fcm_token;
                        $template_notification = NotificationsFirebase::getTemplateNotification(NotificationsFirebase::PROJECT_EXPIRED_HAS_NOT_REACHED_MIN);
                        if ($template_notification) {
                            $template_notification->content = str_replace("{{project_name}}", $value->title, $template_notification->content);
                            $input['title'] = $template_notification->title;
                            $input['content'] = $template_notification->content;
                            $input['partner_id'] =  $value->partner_id;
                            $input['notify_id'] = $template_notification->id;
                        }
                    }
                    if ($number_of_response >= $value->attempts_limit_min && $number_of_response < $value->attempts_limit_max) {
                        $fcm_token = MappingUidFcmToken::getMappingUidFcmTokenByPartnerId($value->partner_id)->fcm_token ?? null;
                        $input['fcm_token'] = $fcm_token;
                        $template_notification = NotificationsFirebase::getTemplateNotification(NotificationsFirebase::PROJECT_EXPIRED_HAS_NOT_REACHED_MAX);
                        if ($template_notification) {
                            $template_notification->content = str_replace("{{project_name}}", $value->title, $template_notification->content);
                            $input['title'] = $template_notification->title;
                            $input['content'] = $template_notification->content;
                            $input['partner_id'] =  $value->partner_id;
                            $input['notify_id'] = $template_notification->id;
                        }
                    }
                    QueueNotifications::create($input);
                }
            }
            return true;
        }
        return false;
    }
}
