<?php

namespace App\Http\Controllers\v1\client;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Models\Survey;
use App\Models\SurveyPartnerInput;
use Carbon\Carbon;
use Jenssegers\Agent\Facades\Agent;



class SurveyPartnerInputAnynomousController extends Controller
{

    public function answerSurveyAnynomous(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), []);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $input['survey_id'] = $request->survey_id;
            $input['state'] = SurveyPartnerInput::STATE_NEW;
            $input['start_datetime'] =  Carbon::now();
            $input['os'] = Agent::device();
            $input['ip'] = $request->ip();
            $input['browser'] = Agent::browser();
            $input['user_agent'] = $request->server('HTTP_USER_AGENT');
            $input['is_anynomous'] = SurveyPartnerInput::ANYNOMOUS_TRUE;
            $survey = Survey::getDetailSurvey($request->survey_id);
            if (!$survey || $survey->state != Survey::STATUS_ON_PROGRESS) {
                return ClientResponse::responseError('Khảo sát không tồn tại hoặc đã đóng');
            }
            $result = SurveyPartnerInput::create($input);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            Survey::updateSurvey(
                [
                    "view" => $survey->view + 1,
                ],
                $request->survey_id
            );
            return ClientResponse::responseSuccess('Thêm mới thành công', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function updateAnswerSurveyAnynomous(Request $request)
    {
        try {

            $partner_input_id = $request->partner_input_id;
            $input_update['start_datetime'] =  Carbon::now();
            $input_update['state'] =  SurveyPartnerInput::STATUS_DONE;
            $result = SurveyPartnerInput::updateSurveyPartnerInput($input_update, $partner_input_id);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Cập nhập thành công', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
