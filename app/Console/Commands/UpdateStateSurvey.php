<?php

namespace App\Console\Commands;

use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateStateSurvey extends Command
{

    protected $signature = 'update-state-survey';

    protected $description = '...';

    public function handle()
    {
        try {
            Survey::updateStateCompleted();
            $list_survey_expired = Survey::listSurveyTimeUp();
            foreach ($list_survey_expired as $survey) {
                $number_of_response  = SurveyPartnerInput::countSurveyInput($survey->survey_id, SurveyPartnerInput::ANYNOMOUS_TRUE);
                if ($number_of_response < $survey->limmit_of_response_anomyous) {
                    Survey::updateSurvey(["state" => Survey::STATUS_NOT_COMPLETED, 'status_not_completed' => Survey::TIME_UP], $survey->survey_id);
                } else {
                    Survey::updateSurvey(["state" => Survey::STATUS_COMPLETED], $survey->survey_id);
                }
            }

            // $list_survey = Survey::listSurvey0nProgress();
            // foreach ($list_survey as $survey) {
            //     $number_of_response  = SurveyPartnerInput::countAllSurveyUserInput($survey->user_id);
            //     if ($number_of_response < $survey->limmit_of_response_anomyous) {
            //         Survey::updateSurvey(["state" => Survey::STATUS_NOT_COMPLETED, 'status_not_completed' => Survey::TIME_UP], $survey->survey_id);
            //     } else {
            //         Survey::updateSurvey(["state" => Survey::STATUS_COMPLETED], $survey->survey_id);
            //     }
            // }
        } catch (\Exception $ex) {
            Log::error("#ERROR: deleteExpire:user-refresh-token " . $ex->getMessage());
        }
    }
}
