<?php

namespace App\Helpers;

use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use App\Models\SurveyPartnerInputLine;
use App\Models\UserPackage;
use Carbon\Carbon;

class DeleteProjectDataStorageExpires
{
    public static function deleteProjectDataStorageExpires()
    {
        $list_project = Survey::getAllSurvey();
        if (is_array($list_project) && count($list_project) > 0) {
            foreach ($list_project as $key => $value) {
                $user_package = UserPackage::getPackageUser($value['user_id'], Carbon::now());
                if ($user_package['data_storage'] > 0) {
                    $time = Carbon::now()->addDays(-$user_package['data_storage'])->toDate()->format('Y-m-d');
                    $start_time = date_format(date_create($value['start_time']), 'Y-m-d');
                    if (strtotime($time) ==  strtotime($start_time)) {
                        $partner_input = SurveyPartnerInput::deleteSurveyPartnerInput($value['id']);
                        foreach ($partner_input as $item) {
                            SurveyPartnerInputLine::deleteAllPartnerInputLine($value['id'], $item->id);
                        }
                        $partner_input->update(['deleted' => SurveyPartnerInput::DELETED]);
                    };
                }
            }
            return true;
        }
        return false;
    }
}
