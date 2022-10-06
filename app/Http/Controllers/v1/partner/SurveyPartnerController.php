<?php

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\Partner;
use App\Models\Survey;
use App\Models\SurveyPartner;
use App\Models\SurveyPartnerInput;
use Carbon\Carbon;

class SurveyPartnerController extends Controller
{

    public function getlistSurveyPartner(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $partner_id = $partner->id ?? 0;
                    $perPage = $request->per_page ?? 5;
                    $page = $request->current_page ?? 1;
                    $time_now = Carbon::now();
                    $datas = SurveyPartner::getlistSurveyPartner($perPage,  $page, $partner_id, $time_now);
                    $datas = RemoveData::removeUnusedData($datas);
                    $array = array();
                    foreach ($datas['data'] as $key => $value) {
                        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $value->end_time)->timestamp;
                        $time_remaining = $timestamp - Carbon::now()->timestamp;
                        $data = json_decode(json_encode($value), true);
                        $data['time_remaining'] = $time_remaining;
                        $array[$key] = $data;
                    }
                    $datas['data'] = $array;
                    if (!$datas) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    return ClientResponse::responseSuccess('OK', $datas);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function getDetailSurveyPartner(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $survey_partner_id = $request->survey_partner_id;
                    $result = SurveyPartner::getDetailSurveyPartner($survey_partner_id);
                    if (!$result) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    Survey::updateSurvey(['view' => $result->view + 1], $result->survey_id);
                    return ClientResponse::responseSuccess('OK', $result);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
